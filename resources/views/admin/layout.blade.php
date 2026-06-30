@vite(['resources/css/app.css', 'resources/js/app.js'])
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&display=swap" rel="stylesheet">
{{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<title>@yield('title', 'Aplikasi UMKM')</title>

<div class="container-fluid px-2 px-md-3 px-lg-4">
    <nav class="navbar navbar-expand-lg admin-topbar mb-4">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('admin.dashboard') }}">
                <span class="brand-icon"><i class="fas fa-layer-group"></i></span>
                <span>
                    <span class="brand-title">UMKM Admin</span>
                    <span class="brand-subtitle">Control Center</span>
                </span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="adminNav">
                <div class="ms-auto d-flex flex-column flex-lg-row align-items-start align-items-lg-center gap-3">
                    <div class="admin-user">
                        <span class="admin-avatar"><i class="fas fa-user"></i></span>
                        <div>
                            <div class="admin-user-label">Administrator</div>
                            <div class="admin-user-name">{{ auth()->user()->name }}</div>
                        </div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-ghost">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <div class="row g-3">
        <div class="col-12 col-lg-2 mb-2 mb-lg-4">
            <div class="admin-sidebar sticky-top">
                <div class="sidebar-head">
                    <div class="sidebar-title">Navigation</div>
                    <div class="sidebar-subtitle">Kelola seluruh data</div>
                </div>
                <div class="list-group">
                    <a href="{{ route('admin.dashboard') }}"
                        class="list-group-item list-group-item-action sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="{{ route('admin.umkm.index') }}"
                        class="list-group-item list-group-item-action sidebar-link {{ request()->routeIs('admin.umkm.*') ? 'active' : '' }}">
                        <i class="fas fa-store"></i>
                        <span>UMKM</span>
                    </a>
                    <a href="{{ route('admin.menu.index') }}"
                        class="list-group-item list-group-item-action sidebar-link {{ request()->routeIs('admin.menu.*') ? 'active' : '' }}">
                        <i class="fas fa-utensils"></i>
                        <span>Menu</span>
                    </a>
                    <a href="{{ route('admin.kategori.index') }}"
                        class="list-group-item list-group-item-action sidebar-link {{ request()->routeIs('admin.kategori.*') ? 'active' : '' }}">
                        <i class="fas fa-tag"></i>
                        <span>Kategori</span>
                    </a>
                    <a href="{{ route('admin.kelompok.index') }}"
                        class="list-group-item list-group-item-action sidebar-link {{ request()->routeIs('admin.kelompok.*') ? 'active' : '' }}">
                        <i class="fas fa-layer-group"></i>
                        <span>Kelompok</span>
                    </a>
                    <a href="{{ route('admin.lokasi.index') }}"
                        class="list-group-item list-group-item-action sidebar-link {{ request()->routeIs('admin.lokasi.*') ? 'active' : '' }}">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Lokasi</span>
                    </a>
                    <a href="{{ route('admin.rating.index') }}"
                        class="list-group-item list-group-item-action sidebar-link {{ request()->routeIs('admin.rating.*') ? 'active' : '' }}">
                        <i class="fas fa-star"></i>
                        <span>Rating</span>
                    </a>
                    <a href="{{ route('admin.user.index') }}"
                        class="list-group-item list-group-item-action sidebar-link {{ request()->routeIs('admin.user.*') ? 'active' : '' }}">
                        <i class="fas fa-users"></i>
                        <span>User</span>
                    </a>
                    <a href="{{ route('admin.activities.index') }}"
                        class="list-group-item list-group-item-action sidebar-link {{ request()->routeIs('admin.activities.*') ? 'active' : '' }}">
                        <i class="fas fa-list"></i>
                        <span>Log Activities</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-10">
            @if (session()->has('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session()->has('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @push('styles')
                @vite('resources/css/refactor.css')
            @endpush
            @yield('admin-content')
        </div>
    </div>
</div>

<style>
    :root {
        --admin-ink: #0f172a;
        --admin-muted: #6b7280;
        --admin-border: #e5e9f2;
        --admin-surface: #f8fafc;
        --admin-accent: #14b8a6;
        --admin-accent-dark: #0f766e;
        --admin-navy: #0b1324;
    }

    body {
        font-family: 'Sora', sans-serif;
        background: linear-gradient(180deg, #f8fafc 0%, #eef2f7 100%);
        color: var(--admin-ink);
    }

    .admin-topbar {
        background: linear-gradient(135deg, #0b1324 0%, #111b34 50%, #0b1324 100%);
        border-radius: 18px;
        padding: 0.7rem 1rem;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.08);
    }

    .admin-topbar .navbar-brand {
        color: #f8fafc;
    }

    .admin-topbar .navbar-brand:hover {
        color: #ffffff;
    }

    .brand-icon {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        background: rgba(20, 184, 166, 0.2);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #5eead4;
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.08);
    }

    .brand-title {
        display: block;
        font-weight: 700;
        letter-spacing: -0.02em;
        line-height: 1.2;
    }

    .brand-subtitle {
        display: block;
        font-size: 0.75rem;
        color: rgba(226, 232, 240, 0.7);
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }

    .admin-topbar .navbar-toggler {
        border: 1px solid rgba(226, 232, 240, 0.4);
    }

    .admin-topbar .navbar-toggler-icon {
        filter: invert(1);
    }

    .admin-user {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.35rem 0.75rem;
        border-radius: 999px;
        background: rgba(15, 23, 42, 0.4);
        color: #e2e8f0;
        border: 1px solid rgba(255, 255, 255, 0.08);
    }

    .admin-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: rgba(94, 234, 212, 0.2);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #5eead4;
        font-size: 0.95rem;
    }

    .admin-user-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: rgba(226, 232, 240, 0.6);
    }

    .admin-user-name {
        font-size: 0.9rem;
        font-weight: 600;
        color: #f8fafc;
        line-height: 1.2;
    }

    .btn-ghost {
        color: #e2e8f0;
        border: 1px solid rgba(226, 232, 240, 0.35);
        background: transparent;
        border-radius: 999px;
        padding: 0.35rem 0.9rem;
        font-weight: 600;
    }

    .btn-ghost:hover {
        color: #ffffff;
        border-color: rgba(255, 255, 255, 0.6);
        background: rgba(255, 255, 255, 0.08);
    }

    .admin-sidebar {
        background: #ffffff;
        border-radius: 18px;
        padding: 1rem;
        border: 1px solid var(--admin-border);
        box-shadow: 0 18px 32px rgba(15, 23, 42, 0.08);
        max-height: calc(100vh - 120px);
        overflow-y: auto;
        top: 1rem;
    }

    .sidebar-head {
        margin-bottom: 1rem;
    }

    .sidebar-title {
        font-weight: 700;
        font-size: 0.95rem;
        color: var(--admin-ink);
    }

    .sidebar-subtitle {
        font-size: 0.78rem;
        color: var(--admin-muted);
    }

    .admin-sidebar .list-group {
        gap: 0.35rem;
    }

    .admin-sidebar .list-group-item {
        border: 1px solid transparent;
        border-radius: 12px !important;
        padding: 0.6rem 0.75rem;
        font-weight: 600;
        color: var(--admin-ink);
        display: flex;
        align-items: center;
        gap: 0.65rem;
        background: transparent;
    }

    .admin-sidebar .list-group-item i {
        color: var(--admin-muted);
        font-size: 0.95rem;
    }

    .admin-sidebar .list-group-item:hover {
        background: #f0f7f6;
        border-color: rgba(20, 184, 166, 0.25);
        color: var(--admin-accent-dark);
    }

    .admin-sidebar .list-group-item.active {
        background: linear-gradient(135deg, rgba(20, 184, 166, 0.18), rgba(20, 184, 166, 0.08));
        border-color: rgba(20, 184, 166, 0.4);
        color: var(--admin-accent-dark);
        box-shadow: inset 3px 0 0 var(--admin-accent);
    }

    .admin-sidebar .list-group-item.active i {
        color: var(--admin-accent-dark);
    }

    .admin-card {
        background: #ffffff;
        border-radius: 18px;
        border: 1px solid var(--admin-border);
        box-shadow: 0 16px 32px rgba(15, 23, 42, 0.08);
        overflow: hidden;
    }

    .admin-card-header {
        padding: 1rem 1.25rem;
        background: #ffffff;
        border-bottom: 1px solid var(--admin-border);
    }

    .admin-card-title {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--admin-ink);
    }

    .admin-card-subtitle {
        font-size: 0.78rem;
        color: var(--admin-muted);
    }

    .admin-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .admin-table-wrapper {
        padding: 0;
    }

    .admin-table {
        margin: 0;
    }

    .admin-table thead th {
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-size: 0.7rem;
        color: var(--admin-muted);
        background: #f8fafc;
        border-bottom: 1px solid var(--admin-border);
        padding: 0.75rem 1rem;
    }

    .admin-table tbody td {
        padding: 0.85rem 1rem;
        border-bottom: 1px solid #edf1f7;
    }

    .admin-table tbody tr:last-child td {
        border-bottom: none;
    }

    .admin-actions {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }

    .admin-actions form {
        margin: 0;
    }

    .btn-icon {
        width: 34px;
        height: 34px;
        padding: 0;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .btn-icon i {
        margin: 0;
    }

    .table-hover tbody tr:hover {
        background-color: #f2f6fc;
    }

    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }

    .table td,
    .table th {
        white-space: nowrap;
        vertical-align: middle;
    }

    /* Constrain images in admin area to keep layout tidy */
    .admin-table img,
    .admin-card img,
    .admin-sidebar img,
    .admin-avatar img {
        width: 96px;
        height: 64px;
        object-fit: cover;
        border-radius: 8px;
        display: inline-block;
    }

    /* Smaller thumbnails in dense tables */
    .admin-table img {
        width: 80px;
        height: 56px;
    }

    @media (max-width: 991.98px) {
        .admin-topbar {
            border-radius: 14px;
        }

        .admin-sidebar {
            position: static !important;
            max-height: none;
            display: flex;
            flex-direction: row;
            overflow-x: auto;
            overflow-y: hidden;
            gap: 0.5rem;
            padding: 0.5rem;
        }

        .admin-sidebar .sidebar-head {
            display: none;
        }

        .admin-sidebar .list-group {
            flex-direction: row;
            gap: 0.5rem;
        }

        .admin-sidebar .list-group-item {
            border-radius: 999px !important;
            min-width: max-content;
            border: 1px solid #d9dee5;
            font-size: 0.85rem;
        }

        .brand-title {
            font-size: 1rem;
        }
    }

    @media (max-width: 576px) {
        .container-fluid {
            padding-left: 0.5rem !important;
            padding-right: 0.5rem !important;
        }

        .btn,
        .form-control,
        .form-select {
            min-height: 38px;
        }
    }
</style>

@stack('styles')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@stack('scripts')
