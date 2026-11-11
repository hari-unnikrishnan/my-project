@extends('layouts.app')
@section('title', 'Your Profile')
@section('page', 'profile')
@section('breadcrumb', 'Profile')
@section('content')
    <section class="hero-panel glass-panel"><div class="hero-copy"><span class="eyebrow">Your account</span><h1>A workspace that feels like yours.</h1><p>Manage your profile details and keep your sign-in secure.</p></div></section>
    <section class="profile-grid">
        <form class="glass-panel tool-form" method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PATCH')
            <span class="section-kicker">Profile details</span><h2>About you</h2>
            <div class="profile-avatar"><span class="profile-initials">{{ strtoupper(substr($user->name, 0, 2)) }}</span><span>{{ $user->email }}</span></div>
            <label class="form-label" for="profile-name">Full name</label><input class="form-control" id="profile-name" name="name" value="{{ old('name', $user->name) }}" autocomplete="name" required>
            <label class="form-label" for="profile-email">Email address</label><input class="form-control" id="profile-email" name="email" type="email" value="{{ old('email', $user->email) }}" autocomplete="username" required>
            <button class="primary-button" type="submit">Save profile</button>
        </form>
        <form class="glass-panel tool-form" method="POST" action="{{ route('password.update') }}">
            @csrf
            @method('PUT')
            <span class="section-kicker">Security</span><h2>Change password</h2>
            <label class="form-label" for="current-password">Current password</label><input class="form-control" id="current-password" name="current_password" type="password" autocomplete="current-password" required>
            <label class="form-label" for="new-password">New password</label><input class="form-control" id="new-password" name="password" type="password" autocomplete="new-password" required>
            <label class="form-label" for="confirm-password">Confirm new password</label><input class="form-control" id="confirm-password" name="password_confirmation" type="password" autocomplete="new-password" required>
            <button class="secondary-button" type="submit">Update password</button>
        </form>
        <form class="glass-panel tool-form" method="POST" action="{{ route('profile.destroy') }}" data-confirm="Permanently delete your account and its saved workspace data?">
            @csrf
            @method('DELETE')
            <span class="section-kicker">Danger zone</span><h2>Delete account</h2>
            <p class="muted-label">This permanently removes your account and associated conversations.</p>
            <label class="form-label" for="delete-password">Confirm with your password</label><input class="form-control" id="delete-password" name="password" type="password" autocomplete="current-password" required>
            <button class="danger-button" type="submit">Delete my account</button>
        </form>
    </section>
@endsection
