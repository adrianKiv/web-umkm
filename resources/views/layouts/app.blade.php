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
                <img src="{{ asset('images/logodark.png') }}" alt="Logo Kuliner UPI" class="me-2"
                    style="height: 50px; width: auto; object-fit: contain;">
                <span class="fw-bold">KULINER UPI</span>
            </a>

            <!-- 2. KANAN: Tombol Auth & Toggler (Muncul di luar collapse) -->
            <div class="d-flex align-items-center gap-2 order-2 order-lg-3 ms-auto ms-lg-0">

                <!-- Kondisi Belum Login -->
                @guest
                    <a href="{{ url('/login') }}" class="btn btn-outline-primary btn-sm rounded-pill px-4 py-2">
                        Login
                    </a>
                @endguest

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

            <!-- 3. BAWAH (Mobile): Search Form -->
            @if (Route::currentRouteName() === 'landing')
                <form action="{{ route('landing') }}" method="GET" id="mobileHeaderSearchForm"
                    class="mobile-live-search d-flex gap-2 d-lg-none w-100 order-3 mt-3">
                    <input type="text" name="search" id="mobileHeaderSearchInput" class="form-control"
                        placeholder="Cari UMKM..." value="{{ request('search') }}">
                    @if (request()->filled('id_kelompok'))
                        <input type="hidden" name="id_kelompok" value="{{ request('id_kelompok') }}">
                    @endif
                    @if (request()->filled('id_kategori'))
                        <input type="hidden" name="id_kategori" value="{{ request('id_kategori') }}">
                    @endif
                    @if (request()->filled('min_rating'))
                        <input type="hidden" name="min_rating" value="{{ request('min_rating') }}">
                    @endif
                    <button class="btn btn-primary px-3 d-flex justify-content-center align-items-center" type="submit"
                        aria-label="Cari">
                        <i class="fas fa-search"></i>
                    </button>
                    <button class="btn btn-outline-secondary px-3 d-flex justify-content-center align-items-center"
                        type="button" id="openMobileFilterBtn" aria-label="Buka filter">
                        <i class="fas fa-sliders-h"></i>
                    </button>
                </form>
            @endif

            <!-- 4. TENGAH (Desktop) & BAWAH (Mobile): Menu Navigasi -->
            <div class="collapse navbar-collapse order-4 order-lg-2" id="navbarNav">

                <div class="mx-auto search-container d-none d-lg-block">
                    @if (Route::currentRouteName() === 'landing')
                        <form action="{{ route('landing') }}" method="GET" id="headerSearchForm"
                            class="d-flex align-items-center gap-2">
                            <input type="text" name="search" id="headerSearchInput" class="form-control"
                                placeholder="Cari seblak, kopi, atau warteg..." value="{{ request('search') }}">

                            <!-- Dropdown Filter Desktop -->
                            <div class="dropdown navbar-filter-dropdown" data-bs-auto-close="outside">
                                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-sliders-h me-1"></i>Filter
                                </button>
                                <div class="dropdown-menu dropdown-menu-end shadow-sm">
                                    <div class="mb-2">
                                        <label class="form-label small mb-1">Kelompok</label>
                                        <select name="id_kelompok"
                                            class="form-select form-select-sm navbar-filter-select">
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
                                        <label class="form-label small mb-1">Kategori</label>
                                        <select name="id_kategori"
                                            class="form-select form-select-sm navbar-filter-select">
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
                                        <label class="form-label small mb-1">Rating Minimal</label>
                                        <select name="min_rating"
                                            class="form-select form-select-sm navbar-filter-select">
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

                                    <div class="navbar-filter-actions">
                                        <button class="btn btn-primary btn-sm" type="submit">Terapkan</button>
                                        <a href="{{ route('landing') }}"
                                            class="btn btn-outline-secondary btn-sm">Reset</a>
                                    </div>
                                </div>
                            </div>

                            <button class="btn btn-primary px-3" type="submit" aria-label="Cari">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    @endif
                </div>

                <!-- Tautan Halaman (Home, E-Map) -->
                <ul class="navbar-nav ms-auto align-items-center mt-3 mt-lg-0">
                    <li class="nav-item">
                        <a class="nav-link px-3" href="{{ url('/') }}">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3" href="{{ route('data-umkm.map') }}">
                            <i class="fas fa-map-marked-alt me-1"></i>E-Map
                        </a>
                    </li>
                </ul>
            </div>

        </div>
    </nav>

    @if (Route::currentRouteName() === 'landing')
        <div class="modal fade" id="mobileLandingFilterModal" tabindex="-1"
            aria-labelledby="mobileLandingFilterModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title" id="mobileLandingFilterModalLabel">
                            <i class="fas fa-sliders-h me-1"></i>Filter Pencarian
                        </h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small mb-1">Kelompok</label>
                            <select id="mobileFilterKelompok" class="form-select form-select-sm">
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
                            <label class="form-label small mb-1">Kategori</label>
                            <select id="mobileFilterKategori" class="form-select form-select-sm">
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
                            <label class="form-label small mb-1">Rating Minimal</label>
                            <select id="mobileFilterRating" class="form-select form-select-sm">
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
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary btn-sm"
                            id="mobileFilterResetBtn">Reset</button>
                        <button type="button" class="btn btn-primary btn-sm"
                            id="mobileFilterApplyBtn">Terapkan</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

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

            @yield('content')
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
