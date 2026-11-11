<x-guest-layout>
<section class="auth-card glass-panel">
    <span class="eyebrow">Account recovery</span>
    <h1>Choose a new password</h1>
    <p class="auth-intro">Create a strong password to secure your Aether AI account.</p>
    <form method="POST" action="{{ route('password.store') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <label class="form-label" for="email">Email address</label>
        <input class="form-control" id="email" name="email" type="email" value="{{ old('email', $request->email) }}" autocomplete="username" required autofocus>
        <x-input-error :messages="$errors->get('email')" class="field-error" />
        <label class="form-label" for="password">New password</label>
        <input class="form-control" id="password" name="password" type="password" autocomplete="new-password" required>
        <x-input-error :messages="$errors->get('password')" class="field-error" />
        <label class="form-label" for="password_confirmation">Confirm new password</label>
        <input class="form-control" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
        <x-input-error :messages="$errors->get('password_confirmation')" class="field-error" />
        <button class="primary-button auth-submit" type="submit">Update password <i class="bi bi-arrow-right"></i></button>
    </form>
    <p class="auth-switch"><a href="{{ route('login') }}"><i class="bi bi-arrow-left"></i> Return to sign in</a></p>
</section>
</x-guest-layout>
