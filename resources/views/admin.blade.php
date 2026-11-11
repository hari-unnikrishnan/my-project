@extends('layouts.app')
@section('title', 'Admin Dashboard')
@section('page', 'admin')
@section('breadcrumb', 'Admin')
@section('content')
    <section class="hero-panel glass-panel admin-hero">
        <div class="hero-copy"><span class="eyebrow">Workspace administration</span><h1>People, access, and governance.</h1><p>Review account access, AI tools, and usage from one protected control center.</p></div>
        <div class="hero-actions"><a class="primary-button" href="{{ route('admin.users') }}">Manage users</a><a class="secondary-button" href="#tool-catalog">Manage AI tools</a></div>
    </section>
    <section class="stat-row">
        <x-stat-card icon="bi-people-fill" label="Members" :value="$stats['users']" detail="Registered accounts" />
        <x-stat-card icon="bi-shield-check" label="Administrators" :value="$stats['admins']" detail="Privileged accounts" />
        <x-stat-card icon="bi-stars" label="AI requests" :value="$stats['requests']" detail="Recorded usage units" />
    </section>
    <section class="content-grid">
        <div class="activity-panel glass-panel">
            <div class="section-header compact"><div><span class="section-kicker">Workspace accounts</span><h2>Recently registered users</h2></div><a href="{{ route('admin.users') }}">All users <i class="bi bi-arrow-right-short"></i></a></div>
            @if ($users->isNotEmpty())
                <div class="table-shell"><table><thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Joined</th></tr></thead><tbody>
                    @foreach ($users as $user)
                        <tr><td><div class="member-cell"><span class="avatar-dot">{{ strtoupper(substr($user->name, 0, 2)) }}</span>{{ $user->name }}</div></td><td>{{ $user->email }}</td><td>{{ $user->is_admin || $user->role === 'admin' ? 'Administrator' : 'Member' }}</td><td>{{ $user->created_at->format('M j, Y') }}</td></tr>
                    @endforeach
                </tbody></table></div>
            @else
                <div class="empty-state"><p>No user accounts are registered yet.</p></div>
            @endif
        </div>
        <aside class="side-column glass-panel tool-output" id="tool-catalog"><span class="section-kicker">Tool catalog</span><h2>{{ $stats['tools'] }} active tools</h2><p class="muted-label">Enable or pause individual AI tools.</p>
            @forelse ($tools as $tool)
                <div class="setting-row"><span><strong>{{ $tool->name }}</strong><small>{{ $tool->category }}</small></span><form method="POST" action="{{ route('admin.tools.update', $tool) }}">@csrf @method('PATCH')<input type="hidden" name="is_active" value="{{ $tool->is_active ? '0' : '1' }}"><button class="status-pill {{ $tool->is_active ? 'success' : 'neutral' }}" type="submit">{{ $tool->is_active ? 'Pause' : 'Enable' }}</button></form></div>
            @empty
                <div class="empty-state"><p>No tools have been configured.</p></div>
            @endforelse
        </aside>
    </section>
    <section class="activity-panel glass-panel"><div class="section-header compact"><div><span class="section-kicker">Platform activity</span><h2>Recent API usage</h2></div></div>
        @if ($usage->isNotEmpty())
            <div class="table-shell"><table><thead><tr><th>User</th><th>Tool</th><th>Requests</th><th>Source</th><th>Updated</th></tr></thead><tbody>
                @foreach ($usage as $record)
                    <tr><td>{{ $record->user?->email ?? 'Deleted user' }}</td><td>{{ $record->tool_name }}</td><td>{{ $record->requests_used }}</td><td>{{ $record->source }}</td><td>{{ $record->updated_at->diffForHumans() }}</td></tr>
                @endforeach
            </tbody></table></div>
        @else
            <div class="empty-state"><p>API usage will appear after the first AI request.</p></div>
        @endif
    </section>
@endsection
