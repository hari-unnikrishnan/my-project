@extends('layouts.app')
@section('title', 'Settings')
@section('page', 'settings')
@section('breadcrumb', 'Settings')
@section('content')
    <section class="hero-panel glass-panel"><div class="hero-copy"><span class="eyebrow">Workspace preferences</span><h1>Make Aether work your way.</h1><p>Choose the settings that keep your workspace comfortable and focused.</p></div></section>
    <form class="settings-grid" method="POST" action="{{ route('settings.update') }}">
        @csrf
        @method('PATCH')
        <article class="glass-panel settings-card"><span class="section-kicker">Appearance</span><h2>Theme</h2><p>Choose the look that works best for you.</p>
            <label class="form-label" for="theme">Color theme</label><select class="form-select" id="theme" name="theme"><option value="dark" @selected($settings->theme === 'dark')>Dark</option><option value="light" @selected($settings->theme === 'light')>Light</option></select>
        </article>
        <article class="glass-panel settings-card"><span class="section-kicker">Notifications</span><h2>Email updates</h2><p>Choose which account updates reach your inbox.</p>
            <label class="setting-row"><span><strong>Product updates</strong><small>Important news about your workspace</small></span><input type="checkbox" name="email_notifications" value="1" @checked($settings->email_notifications)></label>
            <label class="setting-row"><span><strong>Tips and inspiration</strong><small>Occasional ideas for your workflows</small></span><input type="checkbox" name="marketing_emails" value="1" @checked($settings->marketing_emails)></label>
        </article>
        <article class="glass-panel settings-card"><span class="section-kicker">Language & region</span><h2>Localization</h2>
            <label class="form-label" for="language-setting">Language</label><select class="form-select" id="language-setting" name="language"><option value="en" @selected($settings->language === 'en')>English</option></select>
            <label class="form-label" for="timezone-setting">Time zone</label><input class="form-control" id="timezone-setting" name="timezone" value="{{ old('timezone', $settings->timezone) }}" maxlength="64">
        </article>
        <article class="glass-panel settings-card"><span class="section-kicker">Account</span><h2>Profile and security</h2><p>Update your details or sign-in credentials.</p><a class="secondary-button" href="{{ route('profile.edit') }}">Manage profile <i class="bi bi-arrow-up-right"></i></a></article>
        <div class="settings-actions"><button class="primary-button" type="submit">Save preferences</button></div>
    </form>
@endsection
