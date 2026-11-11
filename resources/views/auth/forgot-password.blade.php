<x-guest-layout>
<section class="auth-card glass-panel">
    <span class="eyebrow">Account recovery</span>
    <h1>Forgot your password?</h1>
    <p class="auth-intro">Enter the email address linked to your account. We’ll send you a reset link.</p>
    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <label class="form-label" for="email">Email address</label>
        <input class="form-control" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
        <x-input-error :messages="$errors->get('email')" class="field-error" />
        <button class="primary-button auth-submit" type="submit">Send reset link <i class="bi bi-arrow-right"></i></button>
    </form>
    <p class="auth-switch"><a href="{{ route('login') }}"><i class="bi bi-arrow-left"></i> Return to sign in</a></p>
</section>
</x-guest-layout>
