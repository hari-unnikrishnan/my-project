<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Account access') — Aether AI</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/theme.css') }}">
</head>
<body class="theme-dark">
    <main class="auth-shell">
        <a class="brand auth-brand" href="{{ route('landing') }}">
            <span class="brand-mark">A</span>
            <span class="brand-copy"><strong>Aether AI</strong><small>workspace</small></span>
        </a>
        @if (session('status'))
            <div class="workspace-notice" role="status">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="workspace-notice workspace-notice--error" role="alert">
                <strong>Please review the highlighted information.</strong>
                <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif
        {{ $slot }}
        <footer class="auth-footer">Secure workspace access · <a href="{{ route('landing') }}">Back to home</a></footer>
    </main>
    <div class="preview-status" role="status" aria-live="polite"></div>
    <script src="{{ asset('assets/js/app.js') }}" defer></script>
</body>
</html>
