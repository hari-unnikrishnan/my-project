<aside class="sidebar" id="sidebar">
    <a class="brand" href="{{ route('dashboard') }}">
        <span class="brand-mark">A</span>
        <span class="brand-copy"><strong>Aether AI</strong><small>workspace</small></span>
    </a>
    <nav class="nav-stack" aria-label="Main navigation">
        <a class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><i class="bi bi-grid-1x2-fill"></i><span>Dashboard</span></a>
        <a class="nav-item {{ request()->routeIs('chat.*') ? 'active' : '' }}" href="{{ route('chat.index') }}"><i class="bi bi-chat-dots-fill"></i><span>AI Chat</span></a>
        <a class="nav-item {{ request()->routeIs('tools.writing') ? 'active' : '' }}" href="{{ route('tools.writing') }}"><i class="bi bi-stars"></i><span>AI Writing</span></a>
        <a class="nav-item {{ request()->routeIs('tools.image') ? 'active' : '' }}" href="{{ route('tools.image') }}"><i class="bi bi-image-fill"></i><span>AI Image Generator</span></a>
        <a class="nav-item {{ request()->routeIs('tools.code') ? 'active' : '' }}" href="{{ route('tools.code') }}"><i class="bi bi-code-square"></i><span>AI Code Assistant</span></a>
        <a class="nav-item {{ request()->routeIs('tools.website') ? 'active' : '' }}" href="{{ route('tools.website') }}"><i class="bi bi-window-fullscreen"></i><span>AI Website Builder</span></a>
        <a class="nav-item {{ request()->routeIs('tools.documents') ? 'active' : '' }}" href="{{ route('tools.documents') }}"><i class="bi bi-file-earmark-text-fill"></i><span>AI Document Generator</span></a>
        <a class="nav-item {{ request()->routeIs('tools.automation') ? 'active' : '' }}" href="{{ route('tools.automation') }}"><i class="bi bi-gear-fill"></i><span>AI Automation</span></a>
        <a class="nav-item {{ request()->routeIs('tools.analyzer') ? 'active' : '' }}" href="{{ route('tools.analyzer') }}"><i class="bi bi-bar-chart-line"></i><span>AI Data Analyzer</span></a>
        <a class="nav-item {{ request()->routeIs('chat.history') ? 'active' : '' }}" href="{{ route('chat.history') }}"><i class="bi bi-clock-history"></i><span>History</span></a>
        <a class="nav-item {{ request()->routeIs('settings.*', 'profile.*') ? 'active' : '' }}" href="{{ route('settings.index') }}"><i class="bi bi-sliders"></i><span>Settings</span></a>
        @if (auth()->user()->is_admin || auth()->user()->role === 'admin')
            <a class="nav-item {{ request()->routeIs('admin.*') ? 'active' : '' }}" href="{{ route('admin.index') }}"><i class="bi bi-shield-lock"></i><span>Admin</span></a>
        @endif
    </nav>
    <div class="sidebar-footer">
        <div class="mini-card"><span class="mini-label">Your workspace</span><strong>{{ auth()->user()->email }}</strong></div>
        <form method="POST" action="{{ route('logout') }}" class="sidebar-logout">
            @csrf
            <button type="submit" class="secondary-button"><i class="bi bi-box-arrow-right"></i> Sign out</button>
        </form>
    </div>
</aside>
