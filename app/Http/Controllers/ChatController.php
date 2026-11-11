<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Services\OpenAiException;
use App\Services\OpenAiService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ChatController extends Controller
{
    public function index(Request $request): View
    {
        $conversations = $request->user()->conversations()
            ->withCount('messages')
            ->latest('updated_at')
            ->get();

        return view('chat', [
            'conversations' => $conversations,
            'messages' => collect(),
        ]);
    }

    public function show(Request $request, Conversation $conversation): View
    {
        abort_unless($conversation->user_id === $request->user()->id, 404);

        $conversations = $request->user()->conversations()
            ->withCount('messages')
            ->latest('updated_at')
            ->get();
        $messages = $conversation->messages()->orderBy('id')->get();

        return view('chat', compact('conversations', 'conversation', 'messages'));
    }

    public function history(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $conversations = $request->user()->conversations()
            ->withCount('messages')
            ->when($search !== '', fn ($query) => $query->where('title', 'like', '%'.$search.'%'))
            ->latest('updated_at')
            ->paginate(10)
            ->withQueryString();

        return view('chat-history', compact('conversations', 'search'));
    }

    public function store(Request $request, OpenAiService $openAi): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:12000'],
        ]);
        $prompt = trim($validated['message']);

        try {
            $reply = $openAi->complete([
                ['role' => 'user', 'content' => $prompt],
            ]);
        } catch (OpenAiException $exception) {
            return $this->generationError($request, $exception);
        }

        [$conversation, $messages] = DB::transaction(function () use ($request, $prompt, $reply): array {
            $conversation = $request->user()->conversations()->create([
                'title' => Str::limit($prompt, 70),
                'ai_tool' => 'chat',
            ]);
            $messages = $conversation->messages()->createMany([
                ['user_id' => $request->user()->id, 'role' => 'user', 'content' => $prompt],
                [
                    'user_id' => $request->user()->id,
                    'role' => 'assistant',
                    'content' => $reply['content'],
                    'metadata' => ['model' => $reply['model'], 'usage' => $reply['usage']],
                ],
            ]);
            $request->user()->apiUsages()->create([
                'tool_name' => 'AI Chat',
                'requests_used' => 1,
                'source' => 'chat',
            ]);

            return [$conversation, $messages];
        });

        return $this->generationSuccess($request, $conversation, $messages, true);
    }

    public function storeMessage(Request $request, Conversation $conversation, OpenAiService $openAi): JsonResponse|RedirectResponse
    {
        abort_unless($conversation->user_id === $request->user()->id, 404);
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:12000'],
        ]);
        $prompt = trim($validated['message']);
        $history = $conversation->messages()
            ->latest('id')
            ->limit(30)
            ->get(['role', 'content'])
            ->map(fn (Message $message) => ['role' => $message->role, 'content' => $message->content])
            ->reverse()
            ->values()
            ->all();
        $history[] = ['role' => 'user', 'content' => $prompt];

        try {
            $reply = $openAi->complete($history);
        } catch (OpenAiException $exception) {
            return $this->generationError($request, $exception);
        }

        $messages = DB::transaction(function () use ($request, $conversation, $prompt, $reply): Collection {
            $messages = $conversation->messages()->createMany([
                ['user_id' => $request->user()->id, 'role' => 'user', 'content' => $prompt],
                [
                    'user_id' => $request->user()->id,
                    'role' => 'assistant',
                    'content' => $reply['content'],
                    'metadata' => ['model' => $reply['model'], 'usage' => $reply['usage']],
                ],
            ]);
            $conversation->touch();
            $request->user()->apiUsages()->create([
                'tool_name' => 'AI Chat',
                'requests_used' => 1,
                'source' => 'chat',
            ]);

            return $messages;
        });

        return $this->generationSuccess($request, $conversation, $messages);
    }

    public function destroy(Request $request, Conversation $conversation): RedirectResponse
    {
        abort_unless($conversation->user_id === $request->user()->id, 404);

        $conversation->delete();

        return redirect()->route('chat.index')->with('status', 'Conversation deleted.');
    }

    public function regenerate(Request $request, Conversation $conversation, Message $message, OpenAiService $openAi): RedirectResponse
    {
        abort_unless($conversation->user_id === $request->user()->id, 404);
        abort_unless($message->conversation_id === $conversation->id && $message->role === 'assistant', 404);

        $history = $conversation->messages()
            ->where('id', '<', $message->id)
            ->latest('id')
            ->limit(30)
            ->get(['role', 'content'])
            ->map(fn (Message $item) => ['role' => $item->role, 'content' => $item->content])
            ->reverse()
            ->values()
            ->all();

        if ($history === [] || end($history)['role'] !== 'user') {
            return back()->withErrors(['message' => 'This response cannot be regenerated because its prompt is unavailable.']);
        }

        try {
            $reply = $openAi->complete($history);
        } catch (RuntimeException $exception) {
            return back()->withErrors(['message' => $exception->getMessage()]);
        }

        DB::transaction(function () use ($request, $conversation, $message, $reply): void {
            $message->update([
                'content' => $reply['content'],
                'metadata' => ['model' => $reply['model'], 'usage' => $reply['usage']],
            ]);
            $conversation->touch();
            $request->user()->apiUsages()->create([
                'tool_name' => 'AI Chat',
                'requests_used' => 1,
                'source' => 'chat-regenerate',
            ]);
        });

        return redirect()->route('chat.show', $conversation);
    }

    public function image(Request $request, Conversation $conversation, Message $message): BinaryFileResponse
    {
        abort_unless($conversation->user_id === $request->user()->id, 404);
        abort_unless($message->conversation_id === $conversation->id, 404);

        $path = $message->metadata['image_path'] ?? null;
        abort_unless(
            is_string($path)
            && str_starts_with($path, 'generated-images/'.$request->user()->id.'/')
            && Storage::disk('local')->exists($path),
            404
        );

        return response()->file(Storage::disk('local')->path($path), [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function generationSuccess(
        Request $request,
        Conversation $conversation,
        iterable $messages,
        bool $created = false,
    ): JsonResponse|RedirectResponse {
        if (! $request->expectsJson()) {
            return redirect()->route('chat.show', $conversation);
        }

        return response()->json([
            'conversation_id' => $conversation->id,
            'title' => $conversation->title,
            'redirect_url' => route('chat.show', $conversation),
            'message_url' => route('chat.messages.store', $conversation),
            'messages' => collect($messages)->map(fn (Message $message) => [
                'id' => $message->id,
                'role' => $message->role,
                'content' => $message->content,
                'regenerate_url' => $message->role === 'assistant'
                    ? route('chat.regenerate', [$conversation, $message])
                    : null,
            ])->values(),
        ], $created ? 201 : 200);
    }

    private function generationError(Request $request, OpenAiException $exception): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $exception->getMessage(),
                'errors' => ['message' => [$exception->getMessage()]],
            ], $exception->httpStatus);
        }

        return back()->withInput()->withErrors(['message' => $exception->getMessage()]);
    }
}
