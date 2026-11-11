<?php

namespace App\Http\Controllers;

use App\Models\AiTool;
use App\Models\Conversation;
use App\Services\OpenAiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class ToolsController extends Controller
{
    public function writing(Request $request): View
    {
        return $this->page($request, 'tools.writing', 'content-writer');
    }

    public function code(Request $request): View
    {
        return $this->page($request, 'tools.code', 'code-generator');
    }

    public function image(Request $request): View
    {
        return $this->page($request, 'tools.image', 'image-generator');
    }

    public function website(Request $request): View
    {
        return $this->page($request, 'tools.website', 'website-builder');
    }

    public function documents(Request $request): View
    {
        return $this->page($request, 'tools.documents', 'document-creator');
    }

    public function automation(Request $request): View
    {
        return $this->page($request, 'tools.automation', 'automation');
    }

    public function analyzer(Request $request): View
    {
        return $this->page($request, 'tools.analyzer', 'data-analyzer');
    }

    public function generate(Request $request, OpenAiService $openAi): RedirectResponse
    {
        $validated = $request->validate([
            'tool' => ['required', Rule::in(['writing', 'code', 'image', 'website', 'documents', 'automation', 'analyzer'])],
            'prompt' => ['required', 'string', 'max:12000'],
            'content_type' => ['required_if:tool,writing', 'nullable', Rule::in(['blog', 'website', 'email', 'social', 'seo'])],
            'tone' => ['nullable', 'string', 'max:80'],
            'task' => ['required_if:tool,code', 'nullable', Rule::in(['generate', 'debug', 'explain'])],
            'language' => ['required_if:tool,code', 'nullable', Rule::in(['PHP', 'HTML', 'CSS', 'JavaScript', 'Python', 'SQL'])],
            'code' => ['nullable', 'string', 'max:20000'],
            'style' => ['required_if:tool,image', 'nullable', Rule::in(['Editorial', 'Photorealistic', 'Illustration', '3D render'])],
            'aspect_ratio' => ['nullable', Rule::in(['1024x1024', '1536x1024', '1024x1536'])],
            'project_name' => ['nullable', 'string', 'max:120'],
            'pages' => ['nullable', 'string', 'max:500'],
            'document_type' => ['required_if:tool,documents', 'nullable', Rule::in(['Project proposal', 'Product brief', 'Research report', 'Meeting summary', 'Standard operating procedure'])],
            'title' => ['nullable', 'string', 'max:160'],
            'workflow_name' => ['nullable', 'string', 'max:120'],
            'trigger' => ['required_if:tool,automation', 'nullable', Rule::in(['On a schedule', 'When I run it manually', 'When new content is added'])],
            'dataset' => ['required_if:tool,analyzer', 'nullable', 'string', 'max:20000'],
        ]);

        $slug = [
            'writing' => 'content-writer',
            'code' => 'code-generator',
            'image' => 'image-generator',
            'website' => 'website-builder',
            'documents' => 'document-creator',
            'analyzer' => 'data-analyzer',
            'automation' => 'automation',
        ][$validated['tool']] ?? null;

        if ($slug && AiTool::where('slug', $slug)->where('is_active', false)->exists()) {
            return back()->withInput()->withErrors(['prompt' => 'This AI tool is currently unavailable.']);
        }

        $prompt = $this->buildPrompt($validated);
        $userPrompt = trim($validated['prompt']);
        $imagePath = null;

        try {
            if ($validated['tool'] === 'image') {
                $imageBytes = $openAi->generateImage(
                    $prompt,
                    $validated['aspect_ratio'] ?? '1024x1024'
                );
                $imagePath = 'generated-images/'.$request->user()->id.'/'.Str::uuid().'.png';
                if (! Storage::disk('local')->put($imagePath, $imageBytes)) {
                    throw new RuntimeException('The generated image could not be saved. Please try again.');
                }
                $reply = [
                    'content' => 'Image generated.',
                    'model' => config('services.openai.image_model', 'gpt-image-1'),
                    'usage' => null,
                ];
            } else {
                $reply = $openAi->complete([
                    ['role' => 'system', 'content' => $this->systemPrompt($validated['tool'])],
                    ['role' => 'user', 'content' => $prompt],
                ]);
            }
        } catch (RuntimeException $exception) {
            if ($imagePath) {
                Storage::disk('local')->delete($imagePath);
            }

            return back()->withInput()->withErrors(['prompt' => $exception->getMessage()]);
        }

        $toolName = match ($validated['tool']) {
            'writing' => 'AI Writing',
            'code' => 'AI Code Assistant',
            'image' => 'AI Image Generator',
            'website' => 'AI Website Builder',
            'documents' => 'AI Document Generator',
            'automation' => 'AI Automation',
            'analyzer' => 'AI Data Analyzer',
        };

        try {
            $conversation = DB::transaction(function () use ($request, $validated, $userPrompt, $prompt, $reply, $toolName, $imagePath, $slug): Conversation {
                $conversation = $request->user()->conversations()->create([
                    'title' => Str::limit($validated['title'] ?? $validated['project_name'] ?? $validated['workflow_name'] ?? $userPrompt, 70),
                    'ai_tool' => $slug ?? $validated['tool'],
                ]);
                $assistantMetadata = ['model' => $reply['model'], 'usage' => $reply['usage']];
                if ($imagePath) {
                    $assistantMetadata['image_path'] = $imagePath;
                }

                $conversation->messages()->createMany([
                    ['user_id' => $request->user()->id, 'role' => 'user', 'content' => $prompt],
                    [
                        'user_id' => $request->user()->id,
                        'role' => 'assistant',
                        'content' => $reply['content'],
                        'metadata' => $assistantMetadata,
                    ],
                ]);
                $request->user()->apiUsages()->create([
                    'tool_name' => $toolName,
                    'requests_used' => 1,
                    'source' => 'tool-'.$validated['tool'],
                ]);

                return $conversation;
            });
        } catch (Throwable $exception) {
            if ($imagePath) {
                Storage::disk('local')->delete($imagePath);
            }

            throw $exception;
        }

        $route = match ($validated['tool']) {
            'writing' => 'tools.writing',
            'code' => 'tools.code',
            'image' => 'tools.image',
            'website' => 'tools.website',
            'documents' => 'tools.documents',
            'automation' => 'tools.automation',
            'analyzer' => 'tools.analyzer',
        };

        return redirect()->route($route, ['conversation' => $conversation->id]);
    }

    private function page(Request $request, string $view, ?string $toolSlug): View
    {
        $conversation = null;
        $conversationId = $request->query('conversation');

        if ($conversationId !== null) {
            $validated = validator(['conversation' => $conversationId], [
                'conversation' => ['required', 'integer'],
            ])->validate();
            $conversation = $request->user()->conversations()
                ->with(['messages' => fn ($query) => $query->orderBy('id')])
                ->where('ai_tool', $toolSlug)
                ->findOrFail($validated['conversation']);
        }

        return view($view, ['generation' => $conversation]);
    }

    private function buildPrompt(array $input): string
    {
        $details = match ($input['tool']) {
            'writing' => 'Content type: '.($input['content_type'] ?? 'blog post')."\nTone: ".($input['tone'] ?? 'clear and confident'),
            'code' => 'Task: '.($input['task'] ?? 'generate')."\nLanguage: ".($input['language'] ?? 'PHP')."\nCode context:\n".($input['code'] ?? ''),
            'image' => 'Visual style: '.($input['style'] ?? 'editorial')."\nCreate an image based on this brief: ",
            'website' => 'Project: '.($input['project_name'] ?? '')."\nPages: ".($input['pages'] ?? '')."\nTone: ".($input['tone'] ?? 'confident and clear'),
            'documents' => 'Document type: '.($input['document_type'] ?? 'project proposal')."\nTitle: ".($input['title'] ?? ''),
            'automation' => 'Workflow: '.($input['workflow_name'] ?? '')."\nTrigger: ".($input['trigger'] ?? 'manual'),
            'analyzer' => "Dataset:\n".($input['dataset'] ?? '')."\nAnalysis goal: ",
        };

        return $details."\n\n".$input['prompt'];
    }

    private function systemPrompt(string $tool): string
    {
        return match ($tool) {
            'code' => 'You are Aether AI, a careful software development assistant. Explain assumptions, produce secure and maintainable code, and do not claim to execute code.',
            'website' => 'You are Aether AI, a senior web product designer. Return a practical site outline with clear page hierarchy, messaging, and calls to action.',
            'documents' => 'You are Aether AI, a professional document writer. Produce a clear, structured draft and distinguish facts from assumptions.',
            'automation' => 'You are Aether AI, an automation planner. Propose a safe workflow plan and identify permissions or integrations that would be needed. Do not claim that a workflow is running.',
            'analyzer' => 'You are Aether AI, a careful data analyst. Analyze only the data provided, explain assumptions, distinguish observations from hypotheses, and never invent metrics.',
            default => 'You are Aether AI, a thoughtful writing assistant. Create a polished draft based on the user’s brief without inventing facts.',
        };
    }
}
