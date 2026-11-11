<x-guest-layout>
<section class="auth-card glass-panel">
    <span class="eyebrow">Welcome back</span>
    <h1>Sign in to your workspace</h1>
    <p class="auth-intro">Pick up where your best ideas left off.</p>
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <label class="form-label" for="email">Email address</label>
        <input class="form-control" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
        <x-input-error :messages="$errors->get('email')" class="field-error" />
        <label class="form-label" for="password">Password</label>
        <input class="form-control" id="password" name="password" type="password" autocomplete="current-password" required>
        <x-input-error :messages="$errors->get('password')" class="field-error" />
        <div class="auth-options">
            <label for="remember_me"><input id="remember_me" type="checkbox" name="remember"> Remember me</label>
            <a href="{{ route('password.request') }}">Forgot password?</a>
        </div>
        <button class="primary-button auth-submit" type="submit">Sign in <i class="bi bi-arrow-right"></i></button>
    </form>
    <p class="auth-switch">New to Aether AI? <a href="{{ route('register') }}">Create an account</a></p>
</section>
</x-guest-layout>
