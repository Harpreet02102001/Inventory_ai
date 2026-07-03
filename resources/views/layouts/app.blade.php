<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') — {{ config('app.name', 'Mini Inventory System') }}</title>

    {{-- Vite compiles Bootstrap 5 CSS + Bootstrap Icons + our custom CSS --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>

<body>

    {{-- ══════════════════════════════════════════════════════
         SIDEBAR
         Fixed left sidebar — permission-aware navigation
         Width: 250px (defined in CSS)
         Collapses on toggle via body.sidebar-collapsed class
    ══════════════════════════════════════════════════════ --}}
    <aside id="sidebar">

        {{-- Brand / Logo --}}
        <a href="{{ route('dashboard') }}" class="sidebar-brand">
            <div class="sidebar-brand-icon">
                <i class="bi bi-box-seam text-white fs-5"></i>
            </div>
            <div class="sidebar-brand-text">
                Mini Inventory
                <span>System</span>
            </div>
        </a>

        {{-- Navigation Links (permission-aware) --}}
        <nav class="sidebar-nav">
            @include('layouts.navigation')
        </nav>

        {{-- Sidebar Footer --}}
        <div class="sidebar-footer">
            &copy; {{ date('Y') }} Mini Inventory System
        </div>

    </aside>
    {{-- END SIDEBAR --}}


    {{-- ══════════════════════════════════════════════════════
         MAIN CONTENT AREA
         Takes up remaining width after the 250px sidebar.
         margin-left: 250px (defined in CSS, transitions on collapse)
    ══════════════════════════════════════════════════════ --}}
    <div id="main-content">

        {{-- ── TOP NAVBAR ──────────────────────────────────── --}}
        <nav id="top-navbar">

            {{-- Left: Sidebar toggle button --}}
            <button
                type="button"
                class="btn btn-sm btn-outline-secondary border-0"
                onclick="toggleSidebar()"
                title="Toggle sidebar">
                <i class="bi bi-list fs-5"></i>
            </button>

            {{-- Right: User dropdown + Logout --}}
            <div class="d-flex align-items-center gap-2">

                {{-- User Dropdown --}}
                <div class="dropdown">
                    <button
                        class="btn btn-sm btn-outline-secondary border-0 dropdown-toggle d-flex align-items-center gap-2"
                        type="button"
                        data-bs-toggle="dropdown"
                        aria-expanded="false">
                        {{-- Avatar circle --}}
                        <div
                            class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center"
                            style="width:32px; height:32px;">
                            <i class="bi bi-person text-primary" style="font-size:1rem;"></i>
                        </div>
                        <span class="d-none d-sm-inline text-dark fw-medium" style="font-size:0.875rem;">
                            {{ Auth::user()->name }}
                        </span>
                    </button>

                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border" style="min-width:200px; border-radius:0.65rem;">

                        {{-- User info header --}}
                        <li class="px-3 py-2 border-bottom">
                            <p class="mb-0 text-muted" style="font-size:0.7rem;">Signed in as</p>
                            <p class="mb-1 fw-semibold text-truncate" style="font-size:0.8rem; max-width:160px;">
                                {{ Auth::user()->email }}
                            </p>
                            <span class="badge bg-primary bg-opacity-10 text-primary" style="font-size:0.65rem;">
                                {{ Auth::user()->roles->first()?->display_name ?? 'User' }}
                            </span>
                        </li>

                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="{{ route('profile.edit') }}">
                                <i class="bi bi-person-circle text-muted"></i>
                                Profile
                            </a>
                        </li>

                    </ul>
                </div>
                {{-- End User Dropdown --}}

                {{-- Logout Button --}}
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-sm d-flex align-items-center gap-1">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Logout</span>
                    </button>
                </form>

            </div>
            {{-- End Right Side --}}

        </nav>
        {{-- END TOP NAVBAR --}}


        {{-- ── PAGE CONTENT ────────────────────────────────── --}}
        <div id="page-content">

            {{-- Flash: Success --}}
            @if(session('success'))
            <div class="alert alert-success alert-dismissible alert-auto-dismiss flash-message" role="alert">
                <i class="bi bi-check-circle-fill"></i>
                <span>{{ session('success') }}</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            {{-- Flash: Error --}}
            @if(session('error'))
            <div class="alert alert-danger alert-dismissible alert-auto-dismiss flash-message" role="alert">
                <i class="bi bi-exclamation-circle-fill"></i>
                <span>{{ session('error') }}</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            {{-- Flash: Warning --}}
            @if(session('warning'))
            <div class="alert alert-warning alert-dismissible alert-auto-dismiss flash-message" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span>{{ session('warning') }}</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            {{-- Main page slot — all views extend this layout and yield here --}}
            @yield('content')

        </div>
        {{-- END PAGE CONTENT --}}

    </div>
    {{-- END MAIN CONTENT --}}

    @stack('scripts')

</body>

</html>