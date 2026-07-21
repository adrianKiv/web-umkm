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
        /* NEO-BRUTALISM GLOBAL */
        html, body {
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

        /* NAVBAR NEO */
        .navbar-custom {
            background: #fff;
            border-bottom: 4px solid #000;
            padding: 1rem 0;
            /* Hapus glassmorphism */
            backdrop-filter: none;
        }

        .navbar-brand {
            font-weight: 900;
            color: #000 !important;
            letter-spacing: -0.5px;
            text-transform: uppercase;
        }

        /* DROPDOWN NEO */
        .neo-dropdown-toggle {
            background: #ffde59; /* Kuning terang */
            border: 3px solid #000;
            border-radius: 0 !important;
            color: #000;
            font-weight: 900;
            text-transform: uppercase;
            box-shadow: 4px 4px 0 #000;
            transition: all 0.1s ease;
            text-decoration: none;
            padding: 0.5rem 1rem;
        }

        .neo-dropdown-toggle:active {
            transform: translate(4px, 4px);
            box-shadow: 0 0 0 #000;
        }

        .dropdown-menu.neo-dropdown-menu {
            background: #fff;
            border: 4px solid #000;
            border-radius: 0;
            box-shadow: 8px 8px 0 #000;
            padding: 0;
            margin-top: 12px !important;
        }

        .neo-dropdown-menu .dropdown-item {
            font-weight: 900;
            color: #000;
            text-transform: uppercase;
            padding: 0.8rem 1.2rem;
            border-bottom: 2px solid #000;
            transition: none;
        }

        .neo-dropdown-menu .dropdown-item:last-child {
            border-bottom: none;
        }

        .neo-dropdown-menu .dropdown-item:hover {
            background: #5ad641; /* Hover Hijau */
        }

        .neo-dropdown-menu .dropdown-item.text-danger {
            color: #000 !important;
            background: #ff7675; /* Background merah untuk logout */
        }

        .neo-dropdown-menu .dropdown-item.text-danger:hover {
            background: #d63031;
            color: #fff !important;
        }

        .dropdown-divider.neo-divider {
            border-top: 3px solid #000;
            margin: 0;
            opacity: 1;
        }

        /* MAIN WRAPPER */
        main {
            flex: 1;
            padding-top: 2rem;
            padding-bottom: 4rem;
        }

        /* NEO ALERTS (Untuk Flash Messages di Layout) */
        .neo-alert {
            border: 3px solid #000;
            border-radius: 0;
            color: #000;
            font-weight: 900;
            box-shadow: 4px 4px 0 #000;
            text-transform: uppercase;
        }
        .neo-alert-success { background: #5ad641; }
        .neo-alert-danger { background: #ff7675; }

        /* FOOTER NEO */
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
            transition: 0.2s;
        }

        .footer-link:hover {
            background: #ffde59;
            text-decoration: underline;
            text-decoration-thickness: 2px;
            text-underline-offset: 4px;
        }

        .footer-social {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            border: 3px solid #000;
            background: #38bdf8; /* Biru Neo */
            color: #000;
            box-shadow: 3px 3px 0 #000;
            transition: all 0.1s ease;
            text-decoration: none;
        }

        .footer-social:hover {
            background: #ffde59;
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

        /* RESPONSIVE NAVBAR */
        @media (max-width: 991.98px) {
            main {
                padding-top: 1.15rem;
                padding-bottom: 2rem;
            }
        }
    </style>
    @vite('resources/css/refactor.css')
    @stack('styles')
</head>

<body>
    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container flex-wrap justify-content-between">

            <!-- Brand Logo -->
            <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}">
                <img src="{{ asset('images/logolight.png') }}" alt="Logo Kuliner UPI" class="me-2"
                    style="height: 50px; width: auto; object-fit: contain;">
                <span>KULINER UPI</span>
            </a>

            <!-- Dropdown Profil -->
            @auth
                <div class="dropdown ms-auto">
                    <a class="neo-dropdown-toggle d-flex align-items-center dropdown-toggle"
                        href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle me-1"></i>
                        <span class="d-none d-sm-inline">{{ auth()->user()->name }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end neo-dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="{{ url('/') }}">
                                <i class="fas fa-arrow-left me-2"></i>Kembali
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
                            <hr class="dropdown-divider neo-divider">
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

        </div>
    </nav>

    <!-- KONTEN UTAMA -->
    <main>
        <div class="container">
            <!-- Peringatan / Flash Messages Global -->
            @if (session('success'))
                <div class="alert neo-alert neo-alert-success alert-dismissible fade show mt-2 d-flex justify-content-between align-items-center" role="alert">
                    <div><i class="fas fa-check-circle me-2"></i>{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert neo-alert neo-alert-danger alert-dismissible fade show mt-2 d-flex justify-content-between align-items-center" role="alert">
                    <div><i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('contentprofil')
        </div>
    </main>

    <!-- FOOTER -->
    <footer>
        <div class="container pb-3">
            <div class="row g-4">

                <!-- Brand & Deskripsi -->
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

                <!-- Navigasi -->
                <div class="col-6 col-lg-2 ms-lg-auto">
                    <h6 class="fw-black mb-3 text-dark text-uppercase border-bottom border-dark border-2 pb-2 d-inline-block">Navigasi</h6>
                    <ul class="list-unstyled">
                        <li class="mb-3"><a href="{{ url('/') }}" class="footer-link">Beranda</a></li>
                        <li class="mb-3"><a href="{{ route('data-umkm.map') }}" class="footer-link">Peta Lokasi</a></li>
                        <li class="mb-3"><a href="#" class="footer-link">Daftar UMKM</a></li>
                    </ul>
                </div>

                <!-- Bantuan -->
                <div class="col-6 col-lg-2">
                    <h6 class="fw-black mb-3 text-dark text-uppercase border-bottom border-dark border-2 pb-2 d-inline-block">Bantuan</h6>
                    <ul class="list-unstyled">
                        <li class="mb-3"><a href="#" class="footer-link">Kontak Kami</a></li>
                        <li class="mb-3"><a href="#" class="footer-link">Tentang Project</a></li>
                        <li class="mb-3"><a href="#" class="footer-link">Syarat & Ketentuan</a></li>
                    </ul>
                </div>

            </div>

            <hr class="footer-divider">

            <!-- Copyright -->
            <div class="row align-items-center flex-column-reverse flex-md-row text-center text-md-start gap-3 gap-md-0">
                <div class="col-md-6">
                    <p class="fw-bold text-dark mb-0 text-uppercase">&copy; {{ date('Y') }} Tesis R&D - Kawasan UPI Bandung.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="fw-bold text-dark mb-0 text-uppercase">Developed dengan <i class="fas fa-heart text-danger mx-1" style="-webkit-text-stroke: 1px #000;"></i> untuk UMKM</p>
                </div>
            </div>

        </div>
    </footer>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    @stack('scripts')
</body>
</html>
