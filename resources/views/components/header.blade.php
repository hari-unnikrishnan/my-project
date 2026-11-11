<header class="topbar">
    <div class="topbar-left">
        <button class="icon-button" type="button" data-sidebar-toggle aria-label="Toggle sidebar" aria-expanded="false">
            <i class="bi bi-list"></i>
        </button>
        <div class="topbar-breadcrumbs"><span>Workspace</span><i class="bi bi-chevron-right"></i><strong>@yield('breadcrumb', 'Dashboard')</strong></div>
    </div>
    <div class="topbar-actions">
        <button class="icon-button" type="button" data-theme-toggle aria-label="Toggle theme"><i class="bi bi-sun-fill"></i></button>
        <a class="profile-pill" href="{{ route('profile.edit') }}" aria-label="Open profile">
            <span class="profile-initials">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
            <span><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->is_admin || auth()->user()->role === 'admin' ? 'Admin' : 'Member' }}</small></span>
        </a>
    </div>
</header>
