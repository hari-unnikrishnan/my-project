<x-guest-layout>
<section class="auth-card glass-panel">
    <span class="eyebrow">Start creating</span>
    <h1>Your ideas deserve a better workspace.</h1>
    <p class="auth-intro">Create an account to bring your AI workflows together.</p>
    <form method="POST" action="{{ route('register') }}">
        @csrf
        <label class="form-label" for="name">Full name</label>
        <input class="form-control" id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" required autofocus>
        <x-input-error :messages="$errors->get('name')" class="field-error" />
        <label class="form-label" for="email">Work email</label>
        <input class="form-control" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required>
        <x-input-error :messages="$errors->get('email')" class="field-error" />
        <label class="form-label" for="password">Password</label>
        <input class="form-control" id="password" name="password" type="password" autocomplete="new-password" required>
        <x-input-error :messages="$errors->get('password')" class="field-error" />
        <label class="form-label" for="password_confirmation">Confirm password</label>
        <input class="form-control" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
        <x-input-error :messages="$errors->get('password_confirmation')" class="field-error" />
        <button class="primary-button auth-submit" type="submit">Create account <i class="bi bi-arrow-right"></i></button>
    </form>
    <p class="auth-switch">Already have an account? <a href="{{ route('login') }}">Sign in</a></p>
</section>
</x-guest-layout>
