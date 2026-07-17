<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Katalog UMKM UPI')</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> --}}

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <link rel="icon" type="image/png" href="{{ asset('images/logo1.png') }}">
    <style>
        html,
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
            color: #333;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            /* TAMBAHKAN BARIS INI */
        }

        /* Navbar Styling */
        .navbar-custom {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid #e0e0e0;
            padding: 0.8rem 0;
        }

        .navbar-brand {
            font-weight: 700;
            color: #0d6efd !important;
            letter-spacing: -0.5px;
        }

        .nav-link {
            font-weight: 500;
            color: #555 !important;
            transition: color 0.3s;
        }

        .nav-link:hover {
            color: #0d6efd !important;
        }

        /* Search Bar Header */
        .search-container {
            position: relative;
            max-width: 620px;
            width: 100%;
        }

        .search-container input {
            border-radius: 50px;
            padding-left: 1.5rem;
            border: 1px solid #ddd;
            transition: all 0.3s;
        }

        .search-container input:focus {
            box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
            border-color: #0d6efd;
        }

        .navbar-filter-dropdown .dropdown-menu {
            width: 300px;
            padding: 0.8rem;
        }

        .navbar-filter-select {
            width: 100%;
            font-size: 0.9rem;
        }

        .navbar-filter-actions {
            display: flex;
            gap: 0.5rem;
        }

        .navbar-filter-actions .btn {
            width: 100%;
            font-size: 0.85rem;
        }

        /* Main Content Padding */
        main {
            min-height: 80vh;
            padding-top: 2rem;
            padding-bottom: 3rem;
        }

        /* Footer Styling */
        footer {
            background: #fff;
            border-top: 1px solid #e0e0e0;
            padding: 3rem 0 1.5rem;
        }

        .footer-logo {
            font-weight: 700;
            font-size: 1.2rem;
            color: #333;
            margin-bottom: 1rem;
            display: block;
            text-decoration: none;
        }

        .footer-link {
            color: #666;
            text-decoration: none;
            font-size: 0.9rem;
            transition: 0.3s;
        }

        .footer-link:hover {
            color: #0d6efd;
        }

        .navbar-toggler-icon {
            filter: invert(1);
        }

        @media (max-width: 991.98px) {
            .navbar-custom {
                padding: 0.55rem 0;
            }

            .navbar-toggler-icon {
                filter: invert(1);
            }

            .mobile-live-search {
                margin-top: 0.55rem;
            }

            .mobile-live-search .form-control {
                border-radius: 999px;
            }

            .mobile-live-search .btn {
                border-radius: 999px;
                min-width: 44px;
            }

            .search-container {
                max-width: 100%;
            }

            main {
                padding-top: 1.15rem;
                padding-bottom: 2rem;
            }

            .navbar-nav .nav-item.ms-lg-3 {
                margin-left: 0 !important;
                margin-top: 0.5rem;
            }
        }
    </style>
    @vite('resources/css/refactor.css')
    @stack('styles')
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container flex-wrap">
            <!-- 1. KIRI: Brand Logo -->
            <a class="navbar-brand d-flex align-items-center order-1" href="{{ url('/') }}">
                <img src="{{ asset('images/logolight.png') }}" alt="Logo Kuliner UPI" class="me-2"
                    style="height: 50px; width: auto; object-fit: contain;">
                <span class="fw-bold">KULINER UPI</span>
            </a>

                <!-- Kondisi Sudah Login (Dropdown Profil) -->
                @auth
                    <div class="dropdown">
                        <a class="btn btn-outline-primary btn-sm rounded-pill px-3 dropdown-toggle d-flex align-items-center"
                            href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle me-1"></i>
                            <!-- Nama user disembunyikan di layar super kecil agar tidak menabrak logo -->
                            <span class="d-none d-sm-inline">{{ auth()->user()->name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm position-absolute">
                            <li>
                                <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                    <i class="fas fa-user me-2"></i>Profil
                                </a>
                            </li>
                            @if (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.dashboard') }}">
                                        <i class="fas fa-chart-line me-2"></i>Masuk Dashboard Admin
                                    </a>
                                </li>
                            @endif
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @endauth

                <!-- Toggler Garis Tiga -->
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>
        </div>
    </nav>

    <main>
        <div class="container">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show mt-2" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show mt-2" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('contentprofil')
        </div>
    </main>

    <footer>
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <a href="#" class="footer-logo">DIREKTORI UMKM</a>
                    <p class="text-muted small">Pemberdayaan UMKM Kuliner melalui digitalisasi di kawasan Universitas
                        Pendidikan Indonesia.</p>
                    <div class="d-flex gap-3 mt-3">
                        <a href="#" class="text-muted"><i class="fab fa-instagram fa-lg"></i></a>
                        <a href="#" class="text-muted"><i class="fab fa-facebook fa-lg"></i></a>
                        <a href="#" class="text-muted"><i class="fab fa-whatsapp fa-lg"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 ms-lg-auto">
                    <h6 class="fw-bold mb-3">Navigasi</h6>
                    <ul class="list-unstyled">
                        <li><a href="{{ url('/') }}" class="footer-link">Beranda</a></li>
                        <li><a href="{{ route('data-umkm.map') }}" class="footer-link">Peta Lokasi</a></li>
                        <li><a href="#" class="footer-link">Daftar UMKM</a></li>
                    </ul>
                </div>
                <div class="col-lg-2">
                    <h6 class="fw-bold mb-3">Bantuan</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="footer-link">Kontak Kami</a></li>
                        <li><a href="#" class="footer-link">Tentang Project</a></li>
                        <li><a href="#" class="footer-link">Syarat & Ketentuan</a></li>
                    </ul>
                </div>
            </div>
            <hr class="my-4 text-muted">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <p class="small text-muted mb-0">&copy; {{ date('Y') }} Tesis R&D - Kawasan UPI Bandung.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <p class="small text-muted mb-0">Developed denagn <i class="fas fa-heart text-danger"></i> untuk
                        UMKM</p>
                </div>
            </div>
        </div>
    </footer>

    {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> --}}

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    @stack('scripts')
</body>

</html>
