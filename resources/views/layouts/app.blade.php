<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Aether AI is your connected workspace for thinking, creating, and building.">
    <title>@yield('title', 'Workspace') — Aether AI</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/theme.css') }}">
    @stack('styles')
</head>
<body
    class="{{ auth()->user()->settings?->theme === 'light' ? 'theme-light' : 'theme-dark' }}"
    data-page="@yield('page')"
    data-theme="{{ auth()->user()->settings?->theme ?? 'dark' }}"
    data-theme-endpoint="{{ route('settings.theme') }}"
>
    <div class="app-shell">
        @include('components.sidebar')
        <div class="workspace">
            @include('components.header')
            @if (session('status'))
                <div class="workspace-notice" role="status">
                    {{ match (session('status')) {
                        'profile-updated' => 'Profile updated.',
                        'password-updated' => 'Password updated.',
                        default => session('status'),
                    } }}
                </div>
            @endif
            @if ($errors->any())
                @php
                    $hasOpenAiConfigError = collect($errors->all())
                        ->contains(fn ($error) => str_starts_with($error, 'OpenAI API key is not configured.'));
                @endphp
                <div class="workspace-notice workspace-notice--error" role="alert">
                    @if ($hasOpenAiConfigError)
                        <strong>AI setup required</strong>
                        <p>The request was not sent. To enable real AI responses:</p>
                        <ol>
                            <li>Open the Laravel project-root <code>.env</code> file.</li>
                            <li>Set <code>OPENAI_API_KEY=your_new_key_here</code> using a valid key from your OpenAI account.</li>
                            <li>From the project directory, run <code>php artisan config:clear</code>, then restart <code>php artisan serve</code>.</li>
                        </ol>
                        <p>Keep the real key in <code>.env</code>; do not paste it into chat or source code.</p>
                    @else
                        <strong>Please review the highlighted information.</strong>
                        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    @endif
                </div>
            @endif
            <main class="page-content">
                @yield('content')
            </main>
            @include('components.footer')
        </div>
    </div>
    <div class="preview-status" role="status" aria-live="polite"></div>
    <script src="{{ asset('assets/js/app.js') }}" defer></script>
    @stack('scripts')
</body>
</html>
