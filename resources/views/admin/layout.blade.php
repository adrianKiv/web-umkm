<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<div class="container-fluid px-2 px-md-3 px-lg-4">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('admin.dashboard') }}">
                <i class="fas fa-tachometer-alt me-2"></i>Admin Panel
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="adminNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="nav-link">{{ auth()->user()->name }}</span>
                    </li>
                    <li class="nav-item">
                        <form action="{{ route('logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="nav-link btn btn-link">Logout</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="row g-3">
        <div class="col-12 col-lg-2 mb-2 mb-lg-4">
            <div class="list-group sticky-top admin-sidebar" style="top: 1rem;">
                <a href="{{ route('admin.dashboard') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-home me-2"></i>Dashboard
                </a>
                <a href="{{ route('admin.umkm.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.umkm.*') ? 'active' : '' }}">
                    <i class="fas fa-store me-2"></i>UMKM
                </a>
                <a href="{{ route('admin.menu.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.menu.*') ? 'active' : '' }}">
                    <i class="fas fa-utensils me-2"></i>Menu
                </a>
                <a href="{{ route('admin.kategori.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.kategori.*') ? 'active' : '' }}">
                    <i class="fas fa-tag me-2"></i>Kategori
                </a>
                <a href="{{ route('admin.kelompok.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.kelompok.*') ? 'active' : '' }}">
                    <i class="fas fa-layer-group me-2"></i>Kelompok
                </a>
                <a href="{{ route('admin.lokasi.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.lokasi.*') ? 'active' : '' }}">
                    <i class="fas fa-map-marker-alt me-2"></i>Lokasi
                </a>
                <a href="{{ route('admin.rating.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.rating.*') ? 'active' : '' }}">
                    <i class="fas fa-star me-2"></i>Rating
                </a>
                <a href="{{ route('admin.user.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.user.*') ? 'active' : '' }}">
                    <i class="fas fa-users me-2"></i>User
                </a>
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

            @yield('admin-content')
        </div>
    </div>
</div>

<style>
    body {
        font-family: 'Inter', sans-serif;
        background-color: #f5f5f5;
    }

    .admin-sidebar {
        max-height: calc(100vh - 92px);
        overflow-y: auto;
    }

    .list-group-item.active {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }

    .table-hover tbody tr:hover {
        background-color: #f0f0f0;
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

    @media (max-width: 991.98px) {
        .admin-sidebar {
            position: static !important;
            max-height: none;
            display: flex;
            flex-direction: row;
            overflow-x: auto;
            overflow-y: hidden;
            gap: 0.5rem;
            padding-bottom: 0.25rem;
            background: transparent;
        }

        .admin-sidebar .list-group-item {
            border-radius: 999px !important;
            min-width: max-content;
            border: 1px solid #d9dee5;
            font-size: 0.85rem;
        }

        .navbar-brand {
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
