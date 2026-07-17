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
        <!-- Tambahkan justify-content-between di sini -->
        <div class="container flex-wrap justify-content-between">

            <!-- 1. KIRI: Brand Logo (Hapus kelas order-1) -->
            <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}">
                <img src="{{ asset('images/logolight.png') }}" alt="Logo Kuliner UPI" class="me-2"
                    style="height: 50px; width: auto; object-fit: contain;">
                <span class="fw-bold">KULINER UPI</span>
            </a>

            <!-- Kondisi Sudah Login (Dropdown Profil) -->
            @auth
                <!-- Tambahkan ms-auto di sini untuk mendorong ke ujung kanan -->
                <div class="dropdown ms-auto">
                    <a class="btn btn-outline-primary btn-sm rounded-pill px-3 dropdown-toggle d-flex align-items-center"
                        href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle me-1"></i>
                        <!-- Nama user disembunyikan di layar super kecil agar tidak menabrak logo -->
                        <span class="d-none d-sm-inline">{{ auth()->user()->name }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm position-absolute">
                        <li>
                            <a class="dropdown-item" href="{{ url('/') }}">
                                <i class="fas fa-arrow-left me-2"></i>Kembali
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

            @yield('contentauth')
        </div>
    </main>

<footer class="py-5 mt-auto">
    <div class="container pb-3">
        <div class="row g-4">

            <!-- Kolom 1: Brand & Deskripsi -->
            <div class="col-12 col-lg-5 mb-2 mb-lg-0">
                <a href="#" class="footer-logo d-inline-block fw-bold fs-4 text-dark text-decoration-none mb-3">DIREKTORI UMKM</a>
                <p class="text-muted pe-lg-5 mb-4" style="font-size: 0.9rem; line-height: 1.6;">
                    Pemberdayaan UMKM Kuliner melalui digitalisasi di kawasan Universitas Pendidikan Indonesia.
                </p>
                <div class="d-flex gap-4 mt-2">
                    <a href="#" class="text-muted text-decoration-none"><i class="fab fa-instagram fa-xl"></i></a>
                    <a href="#" class="text-muted text-decoration-none"><i class="fab fa-facebook fa-xl"></i></a>
                    <a href="#" class="text-muted text-decoration-none"><i class="fab fa-whatsapp fa-xl"></i></a>
                </div>
            </div>

            <!-- Kolom 2: Navigasi (Samping Kiri di Mobile) -->
            <div class="col-6 col-lg-2 ms-lg-auto">
                <h6 class="fw-bold mb-3 text-dark">Navigasi</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="{{ url('/') }}" class="footer-link text-muted text-decoration-none" style="font-size: 0.9rem;">Beranda</a></li>
                    <li class="mb-2"><a href="{{ route('data-umkm.map') }}" class="footer-link text-muted text-decoration-none" style="font-size: 0.9rem;">Peta Lokasi</a></li>
                    <li class="mb-2"><a href="#" class="footer-link text-muted text-decoration-none" style="font-size: 0.9rem;">Daftar UMKM</a></li>
                </ul>
            </div>

            <!-- Kolom 3: Bantuan (Samping Kanan di Mobile) -->
            <div class="col-6 col-lg-2">
                <h6 class="fw-bold mb-3 text-dark">Bantuan</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="#" class="footer-link text-muted text-decoration-none" style="font-size: 0.9rem;">Kontak Kami</a></li>
                    <li class="mb-2"><a href="#" class="footer-link text-muted text-decoration-none" style="font-size: 0.9rem;">Tentang Project</a></li>
                    <li class="mb-2"><a href="#" class="footer-link text-muted text-decoration-none" style="font-size: 0.9rem;">Syarat & Ketentuan</a></li>
                </ul>
            </div>

        </div>

        <!-- Garis Pembatas -->
        <hr class="my-4 text-muted opacity-25">

        <!-- Baris Bawah: Copyright & Credits -->
        <div class="row align-items-center flex-column-reverse flex-md-row text-center text-md-start gap-3 gap-md-0">
            <div class="col-md-6">
                <p class="small text-muted mb-0">&copy; {{ date('Y') }} Tesis R&D - Kawasan UPI Bandung.</p>
            </div>
            <div class="col-md-6 text-md-end">
                <p class="small text-muted mb-0">Developed dengan <i class="fas fa-heart text-danger mx-1"></i> untuk UMKM</p>
            </div>
        </div>

    </div>
</footer>

    {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> --}}

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    @stack('scripts')
</body>

</html>
