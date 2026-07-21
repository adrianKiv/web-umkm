<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Katalog UMKM UPI')</title>

    <!-- Font Neo-Brutalism -->
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;600;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="icon" type="image/png" href="{{ asset('images/logo1.png') }}">

    <style>
        /* =========================================
           NEO-BRUTALISM GLOBAL STYLES
           ========================================= */
        html,
        body {
            font-family: 'Space Grotesk', sans-serif;
            background-color: #f4f4f2;
            background-image: radial-gradient(#94a3b8 1px, transparent 1px);
            background-size: 20px 20px;
            color: #000;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Helper Classes */
        .fw-black {
            font-weight: 900 !important;
        }

        /* =========================================
           NAVBAR NEO
           ========================================= */
        .navbar-custom {
            background: #fff;
            border-bottom: 4px solid #000;
            padding: 1rem 0;
            backdrop-filter: none;
            /* Hilangkan blur */
            z-index: 1030;
        }

        .navbar-brand {
            font-weight: 900;
            color: #000 !important;
            letter-spacing: -0.5px;
            text-transform: uppercase;
        }

        .nav-link {
            font-weight: 900;
            color: #000 !important;
            text-transform: uppercase;
            border: 2px solid transparent;
            transition: all 0.1s ease;
        }

        .nav-link:hover {
            background: #ffde59;
            border-color: #000;
            color: #000 !important;
            transform: translate(-2px, -2px);
            box-shadow: 4px 4px 0 #000;
        }

        /* =========================================
           SEARCH BAR & FILTER (DESKTOP & MOBILE)
           ========================================= */
        .search-container {
            position: relative;
            max-width: 620px;
            width: 100%;
        }

        /* Form Search Desktop & Mobile */
        .neo-search-input {
            border: 3px solid #000 !important;
            border-radius: 0 !important;
            padding: 0.6rem 1rem;
            font-weight: 600;
            font-size: 0.95rem;
            color: #000;
            box-shadow: 4px 4px 0 #000;
            transition: all 0.1s ease;
        }

        .neo-search-input:focus {
            outline: none;
            box-shadow: 6px 6px 0 #000;
            transform: translate(-2px, -2px);
        }

        .neo-btn-search,
        .neo-btn-filter {
            border: 3px solid #000;
            border-radius: 0;
            background: #38bdf8;
            /* Biru */
            color: #000;
            font-weight: 900;
            box-shadow: 4px 4px 0 #000;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.1s ease;
        }

        .neo-btn-filter {
            background: #ffde59;
            /* Kuning */
        }

        .neo-btn-search:active,
        .neo-btn-filter:active {
            transform: translate(4px, 4px);
            box-shadow: 0 0 0 #000;
        }

        /* Dropdown Filter Desktop */
        .navbar-filter-dropdown .dropdown-menu {
            width: 320px;
            padding: 1.5rem;
            background: #fff;
            border: 4px solid #000;
            border-radius: 0;
            box-shadow: 8px 8px 0 #000;
            margin-top: 15px !important;
        }

        .navbar-filter-select {
            border: 3px solid #000;
            border-radius: 0;
            font-weight: 600;
            cursor: pointer;
        }

        .navbar-filter-select:focus {
            box-shadow: 4px 4px 0 #000;
            border-color: #000;
        }

        /* =========================================
           TOMBOL AUTH & DROPDOWN PROFIL
           ========================================= */
        .neo-btn-auth {
            background: #5ad641;
            /* Hijau */
            border: 3px solid #000;
            border-radius: 0;
            color: #000;
            font-weight: 900;
            text-transform: uppercase;
            padding: 0.4rem 0.5rem;
            box-shadow: 3px 3px 0 #000;
            text-decoration: none;
            transition: all 0.1s;
        }

        .neo-btn-auth:active {
            transform: translate(3px, 3px);
            box-shadow: 0 0 0 #000;
        }

        .dropdown-menu.neo-dropdown-menu {
            background: #fff;
            border: 4px solid #000;
            border-radius: 0;
            box-shadow: 8px 8px 0 #000;
            padding: 0;
            margin-top: 15px !important;
        }

        .neo-dropdown-menu .dropdown-item {
            font-weight: 900;
            color: #000;
            text-transform: uppercase;
            padding: 0.8rem 1.2rem;
            border-bottom: 3px solid #000;
            transition: none;
        }

        .neo-dropdown-menu .dropdown-item:last-child {
            border-bottom: none;
        }

        .neo-dropdown-menu .dropdown-item:hover {
            background: #ffde59;
        }

        .neo-dropdown-menu .dropdown-item.text-danger:hover {
            background: #ff7675;
            color: #000 !important;
        }

        /* Toggler Mobile */
        .navbar-toggler {
            border: 3px solid #000;
            border-radius: 0;
            background: #fff;
            box-shadow: 3px 3px 0 #000;
            padding: 0.4rem 0.6rem;
        }

        .navbar-toggler-icon {
            filter: invert(1);
        }

        /* =========================================
           MODAL FILTER MOBILE
           ========================================= */
        .neo-modal-content {
            border: 4px solid #000;
            border-radius: 0;
            box-shadow: 8px 8px 0 #000;
            background: #fff;
        }

        .neo-modal-header {
            border-bottom: 4px solid #000;
            background: #ffde59;
            padding: 1rem 1.2rem;
        }

        .neo-modal-footer {
            border-top: 4px solid #000;
            padding: 1rem 1.2rem;
            background: #f4f4f2;
        }

        /* =========================================
           MAIN & ALERTS
           ========================================= */
        main {
            flex: 1;
            padding-top: 2rem;
            padding-bottom: 4rem;
        }

        .neo-alert {
            border: 3px solid #000;
            border-radius: 0;
            color: #000;
            font-weight: 900;
            box-shadow: 4px 4px 0 #000;
            text-transform: uppercase;
        }

        .neo-alert-success {
            background: #5ad641;
        }

        .neo-alert-danger {
            background: #ff7675;
        }

        /* =========================================
           FOOTER NEO
           ========================================= */
        footer {
            background: #fff;
            border-top: 4px solid #000;
            padding: 3rem 0 1.5rem;
            margin-top: auto;
        }

        .footer-logo {
            font-weight: 900;
            font-size: 1.5rem;
            color: #000;
            margin-bottom: 1rem;
            display: block;
            text-decoration: none;
        }

        .footer-link {
            color: #000;
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 900;
            text-transform: uppercase;
            transition: 0.1s;
        }

        .footer-link:hover {
            background: #ffde59;
            border: 2px solid #000;
            padding: 2px 6px;
            margin-left: -8px;
            /* Kompensasi padding */
            box-shadow: 2px 2px 0 #000;
        }

        .footer-social {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            border: 3px solid #000;
            background: #ffde59;
            color: #000;
            box-shadow: 3px 3px 0 #000;
            transition: all 0.1s ease;
            text-decoration: none;
        }

        .footer-social:hover {
            background: #38bdf8;
            color: #000;
        }

        .footer-social:active {
            transform: translate(3px, 3px);
            box-shadow: 0 0 0 #000;
        }

        .footer-divider {
            border-top: 4px solid #000;
            opacity: 1;
            margin: 2rem 0;
        }

        /* RESPONSIVE ADJUSTMENTS */
        @media (max-width: 991.98px) {
            .navbar-custom {
                padding: 0.8rem 0;
            }

            .mobile-live-search {
                margin-top: 1rem;
            }

            .search-container {
                max-width: 100%;
            }

            main {
                padding-top: 1.5rem;
                padding-bottom: 2rem;
            }

            .navbar-nav .nav-item {
                border-bottom: 2px solid #000;
            }

            .navbar-nav .nav-item:last-child {
                border-bottom: none;
            }

            .nav-link {
                padding: 1rem 0 !important;
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
                <img src="{{ asset('images/logodark.png') }}" alt="Logo Kuliner UPI" class="me-2"
                    style="height: 50px; width: auto; object-fit: contain;">
                <span class="fw-black">KULINER UPI</span>
            </a>

            <!-- 2. KANAN: Tombol Auth & Toggler -->
            <div class="d-flex align-items-center gap-3 order-2 order-lg-3 ms-auto ms-lg-0">

                <!-- Belum Login -->
                @guest
                    <a href="{{ url('/login') }}" class="neo-btn-auth">LOGIN</a>
                @endguest

                <!-- Sudah Login -->
                @auth
                    <div class="dropdown">
                        <a class="neo-btn-auth dropdown-toggle d-flex align-items-center bg-white" href="#"
                            role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle me-1"></i>
                            <span class="d-none d-sm-inline">{{ auth()->user()->name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end neo-dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                    <i class="fas fa-user me-2"></i>Profil
                                </a>
                            </li>
                            @if (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.dashboard') }}">
                                        <i class="fas fa-chart-line me-2"></i>Dashboard Admin
                                    </a>
                                </li>
                            @endif
                            <li>
                                <hr class="dropdown-divider m-0 border-dark border-3 opacity-100">
                            </li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger border-0 w-100 text-start">
                                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @endauth

                <!-- Toggler Mobile -->
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>

            <!-- 3. BAWAH (Mobile): Search Form -->
            @if (Route::currentRouteName() === 'landing')
                <form action="{{ route('landing') }}" method="GET" id="mobileHeaderSearchForm"
                    class="mobile-live-search d-flex gap-2 d-lg-none w-100 order-3 mt-3">
                    <input type="text" name="search" id="mobileHeaderSearchInput"
                        class="form-control neo-search-input" placeholder="Cari UMKM..."
                        value="{{ request('search') }}">

                    @if (request()->filled('id_kelompok'))
                        <input type="hidden" name="id_kelompok" value="{{ request('id_kelompok') }}">
                    @endif
                    @if (request()->filled('id_kategori'))
                        <input type="hidden" name="id_kategori" value="{{ request('id_kategori') }}">
                    @endif
                    @if (request()->filled('min_rating'))
                        <input type="hidden" name="min_rating" value="{{ request('min_rating') }}">
                    @endif

                    <button class="btn neo-btn-search px-3" type="submit" aria-label="Cari"><i
                            class="fas fa-search"></i></button>
                    <button class="btn neo-btn-filter px-3" type="button" id="openMobileFilterBtn"
                        aria-label="Buka filter" data-bs-toggle="modal" data-bs-target="#mobileLandingFilterModal"><i
                            class="fas fa-sliders-h"></i></button>
                </form>
            @endif

            <!-- 4. TENGAH (Desktop) & BAWAH (Mobile): Navigasi -->
            <div class="collapse navbar-collapse order-4 order-lg-2" id="navbarNav">

                <div class="mx-auto search-container d-none d-lg-block mt-3 mt-lg-0">
                    @if (Route::currentRouteName() === 'landing')
                        <form action="{{ route('landing') }}" method="GET" id="headerSearchForm"
                            class="d-flex align-items-center gap-2">
                            <input type="text" name="search" id="headerSearchInput"
                                class="form-control neo-search-input" placeholder="CARI SEBLAK, KOPI, WARTEG..."
                                value="{{ request('search') }}">

                            <!-- Dropdown Filter Desktop -->
                            <div class="dropdown navbar-filter-dropdown" data-bs-auto-close="outside">
                                <button class="btn neo-btn-filter px-3" type="button" data-bs-toggle="dropdown"
                                    aria-expanded="false" style="height: 46px;">
                                    <i class="fas fa-sliders-h"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end shadow-none">
                                    <div class="mb-3">
                                        <label class="form-label fw-black text-uppercase mb-1">Kelompok</label>
                                        <select name="id_kelompok" class="form-select navbar-filter-select">
                                            <option value="">Semua Kelompok</option>
                                            @foreach ($kelompokList ?? collect() as $kelompok)
                                                <option value="{{ $kelompok->id_kelompok }}"
                                                    {{ (string) request('id_kelompok') === (string) $kelompok->id_kelompok ? 'selected' : '' }}>
                                                    {{ $kelompok->nama_kelompok }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-black text-uppercase mb-1">Kategori</label>
                                        <select name="id_kategori" class="form-select navbar-filter-select">
                                            <option value="">Semua Kategori</option>
                                            @foreach ($kategoriList ?? collect() as $kategori)
                                                <option value="{{ $kategori->id_kategori }}"
                                                    {{ (string) request('id_kategori') === (string) $kategori->id_kategori ? 'selected' : '' }}>
                                                    {{ $kategori->nama_kategori }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label fw-black text-uppercase mb-1">Rating Minimal</label>
                                        <select name="min_rating" class="form-select navbar-filter-select">
                                            <option value="">Semua Rating</option>
                                            <option value="1"
                                                {{ (string) request('min_rating') === '1' ? 'selected' : '' }}>1.0 ke
                                                atas</option>
                                            <option value="2"
                                                {{ (string) request('min_rating') === '2' ? 'selected' : '' }}>2.0 ke
                                                atas</option>
                                            <option value="3"
                                                {{ (string) request('min_rating') === '3' ? 'selected' : '' }}>3.0 ke
                                                atas</option>
                                            <option value="4"
                                                {{ (string) request('min_rating') === '4' ? 'selected' : '' }}>4.0 ke
                                                atas</option>
                                            <option value="4.5"
                                                {{ (string) request('min_rating') === '4.5' ? 'selected' : '' }}>4.5 ke
                                                atas</option>
                                        </select>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button class="btn w-100"
                                            style="background:#5ad641; border:3px solid #000; font-weight:900;"
                                            type="submit">TERAPKAN</button>
                                        <a href="{{ route('landing') }}" class="btn w-100"
                                            style="background:#fff; border:3px solid #000; font-weight:900;">RESET</a>
                                    </div>
                                </div>
                            </div>

                            <button class="btn neo-btn-search px-4" type="submit" aria-label="Cari"
                                style="height: 46px;">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    @endif
                </div>

                <ul class="navbar-nav ms-auto align-items-lg-center mt-3 mt-lg-0">
                    <li class="nav-item">
                        <a class="nav-link px-lg-3" href="{{ url('/') }}">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-lg-3" href="{{ route('data-umkm.map') }}">
                            <i class="fas fa-map-marked-alt me-1"></i>Peta Lokasi
                        </a>
                    </li>
                </ul>
            </div>

        </div>
    </nav>

    <!-- MODAL FILTER MOBILE (NEO) -->
    @if (Route::currentRouteName() === 'landing')
        <div class="modal fade" id="mobileLandingFilterModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content neo-modal-content">
                    <div class="modal-header neo-modal-header">
                        <h5 class="modal-title fw-black text-uppercase text-dark mb-0">
                            <i class="fas fa-sliders-h me-2"></i>Filter Pencarian
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                            style="border: 2px solid #000; opacity: 1;"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-4">
                            <label class="form-label fw-black text-uppercase mb-2">Kelompok</label>
                            <select id="mobileFilterKelompok" class="form-select navbar-filter-select">
                                <option value="">Semua Kelompok</option>
                                @foreach ($kelompokList ?? collect() as $kelompok)
                                    <option value="{{ $kelompok->id_kelompok }}"
                                        {{ (string) request('id_kelompok') === (string) $kelompok->id_kelompok ? 'selected' : '' }}>
                                        {{ $kelompok->nama_kelompok }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-black text-uppercase mb-2">Kategori</label>
                            <select id="mobileFilterKategori" class="form-select navbar-filter-select">
                                <option value="">Semua Kategori</option>
                                @foreach ($kategoriList ?? collect() as $kategori)
                                    <option value="{{ $kategori->id_kategori }}"
                                        {{ (string) request('id_kategori') === (string) $kategori->id_kategori ? 'selected' : '' }}>
                                        {{ $kategori->nama_kategori }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-black text-uppercase mb-2">Rating Minimal</label>
                            <select id="mobileFilterRating" class="form-select navbar-filter-select">
                                <option value="">Semua Rating</option>
                                <option value="1" {{ (string) request('min_rating') === '1' ? 'selected' : '' }}>
                                    1.0 ke atas</option>
                                <option value="2" {{ (string) request('min_rating') === '2' ? 'selected' : '' }}>
                                    2.0 ke atas</option>
                                <option value="3" {{ (string) request('min_rating') === '3' ? 'selected' : '' }}>
                                    3.0 ke atas</option>
                                <option value="4" {{ (string) request('min_rating') === '4' ? 'selected' : '' }}>
                                    4.0 ke atas</option>
                                <option value="4.5"
                                    {{ (string) request('min_rating') === '4.5' ? 'selected' : '' }}>4.5 ke atas
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer neo-modal-footer">
                        <button type="button" class="btn w-100 mb-2" id="mobileFilterResetBtn"
                            style="background:#fff; border:3px solid #000; font-weight:900;">RESET</button>
                        <button type="button" class="btn w-100 m-0" id="mobileFilterApplyBtn"
                            style="background:#5ad641; border:3px solid #000; font-weight:900;">TERAPKAN</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <main>
        <div class="container">
            @if (session('success'))
                <div id="alert-timer" class="alert neo-alert neo-alert-success alert-dismissible fade show mt-2 d-flex justify-content-between align-items-center"
                    role="alert">
                    <div><i class="fas fa-check-circle me-2"></i>{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div id="alert-timer" class="alert neo-alert neo-alert-danger alert-dismissible fade show mt-2 d-flex justify-content-between align-items-center"
                    role="alert">
                    <div><i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <footer class="py-5 mt-auto">
        <div class="container pb-3">
            <div class="row g-4">

                <div class="col-12 col-lg-5 mb-2 mb-lg-0">
                    <a href="#" class="footer-logo">DIREKTORI UMKM</a>
                    <p class="text-dark fw-bold pe-lg-5 mb-4" style="font-size: 0.95rem; line-height: 1.6;">
                        Pemberdayaan UMKM Kuliner melalui digitalisasi di kawasan Universitas Pendidikan Indonesia.
                    </p>
                    <div class="d-flex gap-3 mt-2">
                        <a href="#" class="footer-social"><i class="fab fa-instagram fa-lg"></i></a>
                        <a href="#" class="footer-social"><i class="fab fa-facebook fa-lg"></i></a>
                        <a href="#" class="footer-social"><i class="fab fa-whatsapp fa-lg"></i></a>
                    </div>
                </div>

                <div class="col-6 col-lg-2 ms-lg-auto">
                    <h6
                        class="fw-black mb-3 text-dark text-uppercase border-bottom border-dark border-2 pb-2 d-inline-block">
                        Navigasi</h6>
                    <ul class="list-unstyled">
                        <li class="mb-3"><a href="{{ url('/') }}" class="footer-link">Beranda</a></li>
                        <li class="mb-3"><a href="{{ route('data-umkm.map') }}" class="footer-link">Peta
                                Lokasi</a></li>
                        <li class="mb-3"><a href="#" class="footer-link">Daftar UMKM</a></li>
                    </ul>
                </div>

                <div class="col-6 col-lg-2">
                    <h6
                        class="fw-black mb-3 text-dark text-uppercase border-bottom border-dark border-2 pb-2 d-inline-block">
                        Bantuan</h6>
                    <ul class="list-unstyled">
                        <li class="mb-3"><a href="#" class="footer-link">Kontak Kami</a></li>
                        <li class="mb-3"><a href="#" class="footer-link">Tentang Project</a></li>
                        <li class="mb-3"><a href="#" class="footer-link">Syarat Ketentuan</a></li>
                    </ul>
                </div>

            </div>

            <hr class="footer-divider">

            <div
                class="row align-items-center flex-column-reverse flex-md-row text-center text-md-start gap-3 gap-md-0">
                <div class="col-md-6">
                    <p class="fw-bold text-dark mb-0 text-uppercase">&copy; {{ date('Y') }} Tesis R&D - Kawasan
                        UPI Bandung.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="fw-bold text-dark mb-0 text-uppercase">Developed dengan <i
                            class="fas fa-fire text-danger mx-1" style="-webkit-text-stroke: 1px #000;"></i> untuk
                        UMKM</p>
                </div>
            </div>

        </div>
    </footer>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    @stack('scripts')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const alert = document.getElementById('alert-timer');

            if (alert) {
                setTimeout(() => {
                    const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                    bsAlert.close();
                }, 5000); // 5000 ms = 5 detik
            }
        });
    </script>
</body>

</html>
