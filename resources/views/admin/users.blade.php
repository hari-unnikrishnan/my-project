@extends('layouts.app')
@section('title', 'User Management')
@section('page', 'admin')
@section('breadcrumb', 'Users')
@section('content')
    <section class="hero-panel glass-panel admin-hero"><div class="hero-copy"><span class="eyebrow">Administration</span><h1>Manage workspace access.</h1><p>Review account records and role assignments. Changes are restricted to administrators.</p></div><div class="hero-actions"><a class="secondary-button" href="{{ route('admin.index') }}">Back to admin</a></div></section>
    <section class="activity-panel glass-panel">
        <div class="section-header compact"><div><span class="section-kicker">Access control</span><h2>Users</h2></div><span class="status-pill neutral">{{ $users->total() }} total</span></div>
        @if ($users->isNotEmpty())
            <div class="table-shell"><table><thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Joined</th><th>Actions</th></tr></thead><tbody>
                @foreach ($users as $user)
                    <tr><td><div class="member-cell"><span class="avatar-dot">{{ strtoupper(substr($user->name, 0, 2)) }}</span>{{ $user->name }}</div></td><td>{{ $user->email }}</td><td>{{ $user->is_admin || $user->role === 'admin' ? 'Administrator' : 'Member' }}</td><td>{{ $user->created_at->format('M j, Y') }}</td><td>
                        @if ($user->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.users.update', $user) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="role" value="{{ $user->is_admin || $user->role === 'admin' ? 'user' : 'admin' }}">
                                <button class="secondary-button" type="submit">{{ $user->is_admin || $user->role === 'admin' ? 'Remove admin' : 'Make admin' }}</button>
                            </form>
                        @else
                            <span class="status-pill neutral">Current account</span>
                        @endif
                    </td></tr>
                @endforeach
            </tbody></table></div>
            <div class="history-pagination">{{ $users->links() }}</div>
        @else
            <div class="empty-state"><span class="tool-icon"><i class="bi bi-people"></i></span><h3>No user accounts yet</h3><p>Registered accounts will appear here.</p></div>
        @endif
    </section>
@endsection
