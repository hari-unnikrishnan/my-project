@extends('layouts.app')

@section('title', 'Dashboard')
@section('page', 'dashboard')
@section('breadcrumb', 'Dashboard')

@section('content')
    <section class="hero-panel glass-panel">
        <div class="hero-copy">
            <span class="eyebrow">Your AI workspace</span>
            <h1>Welcome back, {{ auth()->user()->name }}.</h1>
            <p>Turn rough ideas into launch-ready iterations across chat, content, code, and product workflows.</p>
        </div>
        <form class="hero-prompt glass-surface" method="POST" action="{{ route('chat.store') }}">
            @csrf
            <label class="prompt-label" for="dashboard-message">Ask Aether AI</label>
            <div class="prompt-box">
                <textarea id="dashboard-message" name="message" rows="1" placeholder="What would you like to create today?" required maxlength="12000"></textarea>
                <button class="primary-button" type="submit">Generate <i class="bi bi-arrow-up-right"></i></button>
            </div>
        </form>
        <div class="hero-meta">
            <a class="chip-button" href="{{ route('tools.writing') }}">Content ideas</a>
            <a class="chip-button" href="{{ route('tools.code') }}">Code assistance</a>
            <a class="chip-button" href="{{ route('chat.index') }}">Ask a question</a>
        </div>
    </section>

    <section class="stat-row" aria-label="Your usage statistics">
        <x-stat-card icon="bi-chat-left-text-fill" label="Conversations" :value="$stats['conversations']" detail="Saved in your workspace" />
        <x-stat-card icon="bi-stars" label="AI requests" :value="$stats['requests']" detail="Recorded across your tools" />
        <x-stat-card icon="bi-grid-fill" label="Available tools" :value="$stats['tools']" detail="Ready to explore" />
    </section>

    <section class="content-grid">
        <div class="main-column">
            <div class="section-header">
                <div><span class="section-kicker">AI tools</span><h2>Popular workflows</h2></div>
                <a href="{{ route('tools.writing') }}">Explore tools <i class="bi bi-arrow-right-short"></i></a>
            </div>
            @if ($availableTools->isNotEmpty())
                <div class="tool-grid">
                    @foreach ($availableTools as $tool)
                        <x-tool-card
                            :icon="$tool['icon']"
                            :title="$tool['name']"
                            :description="$tool['description']"
                            :accent="$tool['accent']"
                            :href="$tool['href']"
                        />
                    @endforeach
                </div>
            @else
                <div class="glass-panel empty-state"><span class="tool-icon"><i class="bi bi-stars"></i></span><h3>No AI tools are enabled yet</h3><p>Tools will appear here when an administrator enables them.</p></div>
            @endif
        </div>

        <aside class="side-column">
            <div class="section-header compact">
                <div><span class="section-kicker">Your recent work</span><h2>Conversations</h2></div>
                <a href="{{ route('chat.history') }}">History <i class="bi bi-arrow-right-short"></i></a>
            </div>
            @forelse ($recentConversations as $conversation)
                <a class="context-card conversation-card" href="{{ route('chat.show', $conversation) }}">
                    <div class="conversation-heading"><strong>{{ $conversation->title }}</strong><span class="status-pill neutral">{{ $conversation->messages_count }} messages</span></div>
                    <small>{{ $conversation->updated_at->diffForHumans() }}</small>
                </a>
            @empty
                <div class="glass-panel empty-state"><span class="tool-icon"><i class="bi bi-chat-dots"></i></span><h3>Your next idea starts here</h3><p>Your conversations will be saved here after you start a chat.</p><a class="secondary-button" href="{{ route('chat.index') }}">Start a conversation</a></div>
            @endforelse
        </aside>
    </section>

    <section class="activity-panel glass-panel">
        <div class="section-header compact">
            <div><span class="section-kicker">Workspace activity</span><h2>Recent AI requests</h2></div>
            <a class="text-button" href="{{ route('chat.history') }}">View history</a>
        </div>
        @if ($recentUsage->isNotEmpty())
            <div class="table-shell"><table>
                <thead><tr><th>Tool</th><th>Source</th><th>Requests</th><th>Updated</th></tr></thead>
                <tbody>
                    @foreach ($recentUsage as $usage)
                        <tr><td>{{ $usage->tool_name }}</td><td>{{ $usage->source }}</td><td>{{ $usage->requests_used }}</td><td>{{ $usage->updated_at->diffForHumans() }}</td></tr>
                    @endforeach
                </tbody>
            </table></div>
        @else
            <div class="empty-state"><p>Your real AI usage activity will appear here after your first request.</p></div>
        @endif
    </section>
@endsection
