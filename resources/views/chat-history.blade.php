@extends('layouts.app')
@section('title', 'Conversation History')
@section('page', 'history')
@section('breadcrumb', 'History')
@section('content')
    <section class="hero-panel glass-panel"><div class="hero-copy"><span class="eyebrow">Your workspace</span><h1>Pick up any thread.</h1><p>Find conversations you’ve saved and continue where your thinking left off.</p></div><div class="hero-actions"><a class="primary-button" href="{{ route('chat.index') }}"><i class="bi bi-plus-lg"></i> New conversation</a></div></section>
    <section class="activity-panel glass-panel">
        <div class="section-header compact"><div><span class="section-kicker">Saved activity</span><h2>Conversation history</h2></div>
            <form class="history-search" method="GET" action="{{ route('chat.history') }}"><i class="bi bi-search"></i><input type="search" name="q" value="{{ $search }}" placeholder="Search conversations" aria-label="Search conversations"></form>
        </div>
        @if ($conversations->isNotEmpty())
            <div class="conversation-stack">
                @foreach ($conversations as $conversation)
                    <a class="context-card conversation-card" href="{{ route('chat.show', $conversation) }}">
                        <div class="conversation-heading"><strong>{{ $conversation->title }}</strong><span class="status-pill neutral">{{ $conversation->messages_count }} messages</span></div>
                        <p>{{ $conversation->ai_tool ?: 'AI Chat' }}</p>
                        <small>Updated {{ $conversation->updated_at->diffForHumans() }}</small>
                    </a>
                @endforeach
            </div>
            <div class="history-pagination">{{ $conversations->links() }}</div>
        @else
            <div class="empty-state history-empty"><span class="tool-icon"><i class="bi bi-clock-history"></i></span><h3>{{ $search ? 'No matching conversations' : 'Your conversations will live here' }}</h3><p>{{ $search ? 'Try another search phrase.' : 'Start a conversation in AI Chat and it will appear here for easy access.' }}</p><a class="secondary-button" href="{{ route('chat.index') }}">Open AI Chat</a></div>
        @endif
    </section>
@endsection
