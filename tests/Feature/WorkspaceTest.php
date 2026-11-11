<?php

namespace Tests\Feature;

use App\Models\AiTool;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_unverified_registered_user_can_access_the_workspace(): void
    {
        $user = User::factory()->create(['email_verified_at' => null]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk();
    }

    public function test_auth_pages_render_inside_the_shared_guest_layout(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('<title>Account access — Aether AI</title>', false)
            ->assertSee('assets/css/theme.css');

        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('<title>Account access — Aether AI</title>', false);
    }

    public function test_workspace_pages_render_for_an_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $this->get(route('chat.index'))
            ->assertOk()
            ->assertSee('id="message"', false)
            ->assertSee('name="message"', false)
            ->assertSee('maxlength="12000"', false);

        foreach ([
            'dashboard',
            'chat.index',
            'chat.history',
            'tools.writing',
            'tools.code',
            'tools.image',
            'tools.website',
            'tools.documents',
            'tools.automation',
            'tools.analyzer',
            'profile.edit',
            'settings.index',
            'settings.account',
        ] as $route) {
            $this->get(route($route))->assertOk();
        }
    }

    public function test_chat_generation_persists_both_messages_and_usage(): void
    {
        config(['services.openai.api_key' => 'test-key']);
        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response([
                'id' => 'resp_test',
                'model' => 'gpt-4.1-mini',
                'output' => [[
                    'type' => 'message',
                    'role' => 'assistant',
                    'content' => [['type' => 'output_text', 'text' => 'A useful answer.']],
                ]],
                'usage' => ['total_tokens' => 12],
            ]),
        ]);
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('chat.store'), [
            'message' => 'Help me plan my week.',
        ]);

        $response->assertRedirect();
        $conversation = Conversation::query()->sole();
        $response->assertRedirect(route('chat.show', $conversation));
        $this->assertSame('chat', $conversation->ai_tool);
        $this->assertSame(['user', 'assistant'], $conversation->messages()->orderBy('id')->pluck('role')->all());
        $this->assertSame('A useful answer.', $conversation->messages()->latest('id')->value('content'));
        $this->assertDatabaseHas('api_usage', [
            'user_id' => $user->id,
            'tool_name' => 'AI Chat',
            'source' => 'chat',
            'requests_used' => 1,
        ]);
        Http::assertSent(fn ($request) => $request->url() === 'https://api.openai.com/v1/responses'
            && $request->hasHeader('Authorization', 'Bearer test-key')
            && $request['model'] === 'gpt-4.1-mini'
            && $request['input'][0]['content'] === 'Help me plan my week.');
    }

    public function test_chat_trims_messages_and_rejects_empty_or_over_limit_input(): void
    {
        config(['services.openai.api_key' => 'test-key']);
        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response([
                'model' => 'gpt-4.1-mini',
                'output' => [[
                    'type' => 'message',
                    'role' => 'assistant',
                    'content' => [['type' => 'output_text', 'text' => 'Trimmed and saved.']],
                ]],
            ]),
        ]);
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->postJson(route('chat.store'), ['message' => "  Hello there. \n"])
            ->assertCreated();
        $this->assertDatabaseHas('messages', [
            'role' => 'user',
            'content' => 'Hello there.',
        ]);
        Http::assertSent(fn ($request) => $request['input'][0]['content'] === 'Hello there.');

        $this->postJson(route('chat.store'), ['message' => " \n\t "])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('message');
        $this->postJson(route('chat.store'), ['message' => str_repeat('a', 12001)])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('message');
    }

    public function test_chat_json_request_returns_the_saved_messages_and_history_url(): void
    {
        config(['services.openai.api_key' => 'test-key']);
        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response([
                'model' => 'gpt-4.1-mini',
                'output' => [[
                    'type' => 'message',
                    'role' => 'assistant',
                    'content' => [['type' => 'output_text', 'text' => 'Hello, I am Aether AI.']],
                ]],
                'usage' => ['total_tokens' => 9],
            ]),
        ]);
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('chat.store'), [
            'message' => 'Hello, introduce yourself.',
        ]);

        $response->assertCreated();
        $conversation = Conversation::query()->sole();
        $response->assertJsonPath('conversation_id', $conversation->id)
            ->assertJsonPath('messages.1.content', 'Hello, I am Aether AI.')
            ->assertJsonPath('messages.1.role', 'assistant')
            ->assertJsonPath('redirect_url', route('chat.show', $conversation));
        $this->assertDatabaseCount('messages', 2);

        $this->get(route('chat.show', $conversation))
            ->assertOk()
            ->assertSee('Hello, introduce yourself.')
            ->assertSee('Hello, I am Aether AI.');
    }

    public function test_follow_up_chat_request_sends_saved_context_and_persists_the_turn(): void
    {
        config(['services.openai.api_key' => 'test-key']);
        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response([
                'model' => 'gpt-4.1-mini',
                'output' => [[
                    'type' => 'message',
                    'role' => 'assistant',
                    'content' => [['type' => 'output_text', 'text' => 'Here is the next step.']],
                ]],
                'usage' => ['total_tokens' => 11],
            ]),
        ]);
        $user = User::factory()->create();
        $conversation = $user->conversations()->create(['title' => 'Planning', 'ai_tool' => 'chat']);
        $conversation->messages()->createMany([
            ['user_id' => $user->id, 'role' => 'user', 'content' => 'Help me plan.'],
            ['user_id' => $user->id, 'role' => 'assistant', 'content' => 'Start with your goal.'],
        ]);

        $response = $this->actingAs($user)->postJson(route('chat.messages.store', $conversation), [
            'message' => 'What comes next?',
        ]);

        $response->assertOk()
            ->assertJsonPath('messages.1.content', 'Here is the next step.');
        $this->assertDatabaseCount('messages', 4);
        Http::assertSent(fn ($request) => $request->url() === 'https://api.openai.com/v1/responses'
            && collect($request['input'])->contains(fn ($item) => $item['content'] === 'Start with your goal.')
            && collect($request['input'])->last()['content'] === 'What comes next?');
    }

    public function test_chat_reports_a_helpful_json_error_when_the_api_key_is_missing(): void
    {
        config(['services.openai.api_key' => '']);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('chat.store'), ['message' => 'Hello.'])
            ->assertStatus(503)
            ->assertJsonPath(
                'message',
                'OpenAI API key is not configured. Add it to the project-root .env file as OPENAI_API_KEY=your_new_key_here, then run php artisan config:clear and restart php artisan serve from the project directory. Put the real key only in .env, never in chat or source code.'
            );

        $this->assertDatabaseCount('conversations', 0);
        $this->assertDatabaseCount('messages', 0);
    }

    public function test_chat_html_shows_actionable_setup_steps_when_the_openai_key_is_missing(): void
    {
        config(['services.openai.api_key' => '']);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->followingRedirects()
            ->from(route('chat.index'))
            ->post(route('chat.store'), ['message' => 'Hello.'])
            ->assertOk()
            ->assertSee('AI setup required')
            ->assertSee('OPENAI_API_KEY=your_new_key_here')
            ->assertSee('php artisan config:clear')
            ->assertSee('restart')
            ->assertSee('do not paste it into chat or source code');

        $this->assertDatabaseCount('conversations', 0);
        $this->assertDatabaseCount('messages', 0);
    }

    public function test_openai_authentication_quota_and_rate_limit_errors_are_mapped_safely(): void
    {
        config(['services.openai.api_key' => 'test-key']);
        Http::fake([
            'https://api.openai.com/v1/responses' => Http::sequence()
                ->push(['error' => ['code' => 'invalid_api_key']], 401)
                ->push(['error' => ['code' => 'insufficient_quota']], 429)
                ->push(['error' => ['code' => 'rate_limit_exceeded']], 429)
                ->push(['error' => ['code' => 'invalid_request_error']], 400),
        ]);
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->postJson(route('chat.store'), ['message' => 'First request'])
            ->assertStatus(502)
            ->assertJsonPath('message', 'OpenAI authentication failed. Please verify your API key.');
        $this->postJson(route('chat.store'), ['message' => 'Second request'])
            ->assertStatus(503)
            ->assertJsonPath('message', 'Your OpenAI API account has insufficient available quota.');
        $this->postJson(route('chat.store'), ['message' => 'Third request'])
            ->assertStatus(429)
            ->assertJsonPath('message', 'Too many AI requests. Please try again shortly.');
        $this->postJson(route('chat.store'), ['message' => 'Fourth request'])
            ->assertStatus(422)
            ->assertJsonPath('message', 'The AI request could not be processed. Check your message and try again.');
        $this->assertDatabaseCount('conversations', 0);
    }

    public function test_chat_reports_network_failures_without_persisting_an_incomplete_turn(): void
    {
        config(['services.openai.api_key' => 'test-key']);
        Http::fake(fn () => throw new ConnectionException('connection failed'));
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('chat.store'), ['message' => 'Hello.'])
            ->assertStatus(503)
            ->assertJsonPath('message', 'Unable to connect to the AI service. Please try again.');

        $this->assertDatabaseCount('conversations', 0);
    }

    public function test_data_analysis_saves_the_dataset_and_uses_its_own_tool_route(): void
    {
        config(['services.openai.api_key' => 'test-key']);
        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response([
                'model' => 'gpt-4.1-mini',
                'output' => [[
                    'type' => 'message',
                    'role' => 'assistant',
                    'content' => [['type' => 'output_text', 'text' => 'The values show a steady increase.']],
                ]],
                'usage' => ['total_tokens' => 14],
            ]),
        ]);
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('tools.generate'), [
            'tool' => 'analyzer',
            'prompt' => 'Summarize the trend.',
            'dataset' => "month,value\nJanuary,4\nFebruary,8",
        ]);

        $conversation = Conversation::query()->sole();
        $response->assertRedirect(route('tools.analyzer', ['conversation' => $conversation->id]));
        $this->assertSame('data-analyzer', $conversation->ai_tool);
        $this->assertStringContainsString('January,4', $conversation->messages()->oldest('id')->value('content'));
        $this->actingAs($user)
            ->get(route('tools.analyzer', ['conversation' => $conversation->id]))
            ->assertOk()
            ->assertSee('The values show a steady increase.');
        $this->assertDatabaseHas('api_usage', [
            'user_id' => $user->id,
            'tool_name' => 'AI Data Analyzer',
            'source' => 'tool-analyzer',
        ]);
    }

    public function test_generated_images_are_private_and_retrievable_only_by_the_owner(): void
    {
        config(['services.openai.api_key' => 'test-key']);
        Storage::fake('local');
        Http::fake([
            'api.openai.com/*' => Http::response([
                'data' => [['b64_json' => base64_encode('image-bytes')]],
            ]),
        ]);
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $response = $this->actingAs($owner)->post(route('tools.generate'), [
            'tool' => 'image',
            'prompt' => 'A violet glass orb.',
            'style' => 'Illustration',
            'aspect_ratio' => '1024x1024',
        ]);

        $conversation = Conversation::query()->sole();
        $message = $conversation->messages()->where('role', 'assistant')->sole();
        $path = $message->metadata['image_path'];
        Storage::disk('local')->assertExists($path);
        $response->assertRedirect(route('tools.image', ['conversation' => $conversation->id]));

        $this->actingAs($owner)
            ->get(route('chat.image', [$conversation, $message]))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png');

        $this->actingAs($otherUser)
            ->get(route('chat.image', [$conversation, $message]))
            ->assertNotFound();
    }

    public function test_user_cannot_open_another_users_conversation(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $conversation = $owner->conversations()->create([
            'title' => 'Private conversation',
            'ai_tool' => 'chat',
        ]);

        $this->actingAs($otherUser)
            ->get(route('chat.show', $conversation))
            ->assertNotFound();
    }

    public function test_admin_dashboard_reports_database_backed_workspace_totals(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin', 'is_admin' => true]);
        AiTool::create([
            'name' => 'Content Writer',
            'slug' => 'content-writer',
            'category' => 'writing',
            'description' => 'Write content.',
            'is_active' => true,
        ]);

        foreach ([
            ['user' => $user, 'tool' => 'chat'],
            ['user' => $user, 'tool' => 'content-writer'],
            ['user' => $admin, 'tool' => 'image-generator'],
        ] as $conversationData) {
            $conversation = $conversationData['user']->conversations()->create([
                'title' => 'Saved output',
                'ai_tool' => $conversationData['tool'],
            ]);
            $conversation->messages()->create([
                'user_id' => $conversationData['user']->id,
                'role' => 'assistant',
                'content' => 'Generated response.',
            ]);
        }

        $user->apiUsages()->create([
            'tool_name' => 'AI Chat',
            'requests_used' => 5,
            'source' => 'chat',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.index'));

        $response->assertOk();
        $stats = $response->viewData('stats');
        $this->assertSame(2, $stats['users']);
        $this->assertSame(1, $stats['admins']);
        $this->assertSame(5, $stats['requests']);
        $this->assertSame(1, $stats['tools']);
        $this->assertSame(3, $stats['conversations']);
        $this->assertSame(3, $stats['ai_messages']);
        $this->assertSame(2, $stats['generated_contents']);
    }

    public function test_user_settings_are_unique_per_user(): void
    {
        $user = User::factory()->create();

        $user->settings()->create();

        $this->expectException(QueryException::class);
        $user->settings()->create();
    }

    public function test_admin_can_pause_a_tool_and_regular_users_cannot_manage_tools(): void
    {
        $tool = AiTool::create([
            'name' => 'Content Writer',
            'slug' => 'content-writer',
            'category' => 'writing',
            'icon' => 'bi-pen-fill',
            'description' => 'Write content.',
            'accent_class' => 'purple',
            'is_active' => true,
        ]);
        $user = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin', 'is_admin' => true]);

        $this->actingAs($user)
            ->patch(route('admin.tools.update', $tool), ['is_active' => '0'])
            ->assertForbidden();

        $this->actingAs($admin)
            ->patch(route('admin.tools.update', $tool), ['is_active' => '0'])
            ->assertRedirect()
            ->assertSessionHas('status', 'AI tool availability updated.');

        $this->assertDatabaseHas('ai_tools', ['id' => $tool->id, 'is_active' => false]);
    }
}
