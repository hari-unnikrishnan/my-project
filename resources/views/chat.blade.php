@extends('layouts.app')

@section('title', 'AI Chat')
@section('page', 'chat')
@section('breadcrumb', 'AI Chat')

@section('content')
    <div class="chat-layout">
        <aside class="conversation-panel glass-panel">
            <div class="section-header compact">
                <div><span class="section-kicker">Your workspace</span><h2>Conversations</h2></div>
                <a class="icon-button small" href="{{ route('chat.index') }}" aria-label="New conversation"><i class="bi bi-plus-lg"></i></a>
            </div>
            @if ($conversations->isNotEmpty())
                <div class="conversation-list">
                    @foreach ($conversations as $item)
                        <a class="context-card {{ isset($conversation) && $conversation->is($item) ? 'active' : '' }}" href="{{ route('chat.show', $item) }}">
                            <div class="context-topline"><strong>{{ $item->title }}</strong><span class="status-pill neutral">{{ $item->messages_count }}</span></div>
                            <small>{{ $item->updated_at->diffForHumans() }}</small>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="empty-state compact-empty"><span class="tool-icon"><i class="bi bi-chat-dots"></i></span><p>Your conversations will be saved here.</p></div>
            @endif
            <div class="suggestion-box">
                <span class="section-kicker">Suggested prompts</span>
                <div class="suggestion-list">
                    <button class="suggestion-pill" type="button" data-suggest="Help me clarify the main idea and identify the next steps.">Clarify an idea</button>
                    <button class="suggestion-pill" type="button" data-suggest="Review this plan and point out assumptions I should check.">Review a plan</button>
                    <button class="suggestion-pill" type="button" data-suggest="Help me outline a clear onboarding flow for a new product.">Outline a flow</button>
                </div>
            </div>
        </aside>

        <section class="chat-panel glass-panel">
            <div class="chat-header">
                <div><span class="section-kicker" data-chat-context>{{ isset($conversation) ? 'Saved conversation' : 'New conversation' }}</span><h2 data-chat-heading>{{ $conversation->title ?? 'What are we working on?' }}</h2></div>
                @isset($conversation)
                    <form method="POST" action="{{ route('chat.destroy', $conversation) }}" data-confirm="Delete this conversation and all of its messages?">
                        @csrf
                        @method('DELETE')
                        <button class="icon-button" type="submit" aria-label="Delete conversation"><i class="bi bi-trash3"></i></button>
                    </form>
                @endisset
            </div>

            <div class="workspace-notice workspace-notice--error" data-chat-error role="alert" hidden></div>
            <div class="chat-messages" data-chat-messages>
                @forelse ($messages as $message)
                    <article class="message {{ $message->role === 'user' ? 'message-user' : 'message-assistant' }}">
                        <div class="message-avatar {{ $message->role === 'user' ? 'message-avatar--user' : 'message-avatar--assistant' }}">{{ $message->role === 'user' ? strtoupper(substr(auth()->user()->name, 0, 1)) : 'A' }}</div>
                        <div class="message-bubble {{ $message->role === 'assistant' ? 'message-bubble--assistant' : '' }}">
                            <p class="message-content">{{ $message->content }}</p>
                            @if ($message->role === 'assistant')
                                <div class="message-actions">
                                    <button class="mini-action" type="button" data-copy-text aria-label="Copy response"><i class="bi bi-clipboard"></i> Copy</button>
                                    <form method="POST" action="{{ route('chat.regenerate', [$conversation, $message]) }}">
                                        @csrf
                                        <button class="mini-action" type="submit" aria-label="Regenerate response"><i class="bi bi-arrow-clockwise"></i> Regenerate</button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="chat-welcome">
                        <span class="tool-icon"><i class="bi bi-stars"></i></span>
                        <h3>A thoughtful space for your next idea.</h3>
                        <p>Ask a question, explore a direction, or bring a challenge to work through.</p>
                    </div>
                @endforelse
            </div>

            <form class="composer" method="POST" action="{{ isset($conversation) ? route('chat.messages.store', $conversation) : route('chat.store') }}" data-chat-form data-user-initial="{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}">
                @csrf
                <div class="composer-tools"><span class="icon-button ghost" aria-hidden="true"><i class="bi bi-stars"></i></span></div>
                <textarea id="message" data-chat-input name="message" rows="1" maxlength="12000" placeholder="Message Aether AI" required>{{ old('message') }}</textarea>
                <button class="primary-button" type="submit"><i class="bi bi-arrow-up"></i> Send</button>
            </form>
            <p class="chat-disclaimer">AI responses can be inaccurate. Review important information before you act.</p>
        </section>
    </div>
@endsection
