@extends('layouts.app')

@section('title', 'UMKM Dashboard - Temukan Usaha Terdekat')

@section('content')
    @php
        $defaultUmkmImage = asset('images/default-umkm.svg');
    @endphp
    <div class="container-fluid p-0">
        <!-- Hero Section with Search -->
<section class="hero-section d-flex align-items-center position-relative py-4 py-md-5">
    <div class="container" style="position: relative; z-index: 2;">
        <div class="row justify-content-center">

            {{-- Lebar kolom disesuaikan: Penuh di HP (col-12), sedikit mengecil di Tablet/PC --}}
            <div class="col-12 col-md-10 col-lg-8">

                {{-- KOTAK UTAMA (Efek Card Modern) --}}
                <div class="text-center text-white rounded-4 p-4 p-md-5 shadow-lg mx-auto" style="background-color: #1a3547;">

                    {{-- Judul: Menggunakan fs-1 agar otomatis menyesuaikan layar (tidak raksasa di HP) --}}
                    <h1 class="fw-bold mb-3 fs-1">Temukan UMKM Terdekat</h1>

                    <p class="mb-4 text-light" style="font-size: 1.1rem;">
                        Jelajahi berbagai usaha mikro kecil menengah di sekitar Anda dengan mudah dan cepat
                    </p>

                    {{-- Tombol: Menggunakan Flexbox yang sudah diperbaiki --}}
                    <div class="mt-4 d-flex flex-column flex-sm-row justify-content-center gap-3">

                        {{-- Tombol 1 --}}
                        <a href="{{ route('data-umkm.map') }}" class="btn btn-light btn-lg px-4 shadow-sm">
                            <i class="fas fa-map-marked-alt me-2 text-primary"></i>Lihat Semua di Peta
                        </a>

                        {{-- Tombol 2 --}}
                        <button type="button" class="btn btn-outline-light btn-lg px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#umkmSubmissionModal">
                            <i class="fas fa-plus-circle me-2"></i>Daftarkan UMKM
                        </button>

                    </div>

                </div>
                {{-- Akhir Kotak Utama --}}

            </div>
        </div>
    </div>
</section>
        <!-- Recommended UMKM Section -->
        @if ($recommendedUmkms->isNotEmpty())
            <section class="recommended-section py-5 bg-light">
                <div class="container">
                    <div class="row mb-4">
                        <div class="col-12 text-center">
                            <h2 class="h3 mb-3">
                                <i class="fas fa-star text-warning me-2"></i>
                                Rekomendasi Terbaik
                            </h2>
                            <p class="text-muted">UMKM pilihan dengan rating tertinggi dari pelanggan</p>
                        </div>
                    </div>

                    <div class="row g-4 d-none d-md-flex">
                        @foreach ($recommendedUmkms as $umkm)
                            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                                <div class="recommended-card card h-100 border-0 shadow-sm">
                                    <!-- Image Placeholder -->
                                    <div class="card-img-container position-relative">
                                        <img src="{{ $umkm->foto_umkm_url }}" alt="Foto {{ $umkm->nama_umkm }}"
                                            class="recommended-image"
                                            onerror="this.onerror=null;this.src='{{ $defaultUmkmImage }}';">
                                        <div class="recommended-badge">
                                            <i class="fas fa-crown text-warning"></i>
                                        </div>
                                    </div>

                                    <div class="card-body d-flex flex-column text-center">
                                        <h6 class="card-title mb-1">{{ Str::limit($umkm->nama_umkm, 25) }}</h6>

                                        <!-- Category Badge -->
                                        <div class="mb-2">
                                            <small class="badge bg-primary">
                                                <i class="fas fa-tag me-1"></i>
                                                {{ optional($umkm->kategori)->nama_kategori ?? 'Tidak dikategorikan' }}
                                            </small>
                                        </div>

                                        <!-- Rating -->
                                        <div class="mb-2">
                                            <div class="stars justify-content-center">
                                                @php
                                                    $avgRating = $umkm->rating->avg('nilai_rating') ?? 0;
                                                    $ratingCount = $umkm->rating->count();
                                                @endphp
                                                @for ($i = 1; $i <= 5; $i++)
                                                    @if ($i <= floor($avgRating))
                                                        <i class="fas fa-star text-warning"></i>
                                                    @elseif($i - 0.5 <= $avgRating)
                                                        <i class="fas fa-star-half-alt text-warning"></i>
                                                    @else
                                                        <i class="far fa-star text-warning"></i>
                                                    @endif
                                                @endfor
                                                <small class="text-muted d-block">
                                                    {{ number_format($avgRating, 1) }} ({{ $ratingCount }} ulasan)
                                                </small>
                                            </div>
                                        </div>

                                        <!-- Address -->
                                        <div class="mb-3">
                                            <small class="text-muted">
                                                <i class="fas fa-map-marker-alt me-1"></i>
                                                {{ Str::limit($umkm->alamat_lengkap, 30) }}
                                            </small>
                                        </div>
                                    </div>

                                    <!-- Card Footer with Action -->
                                    <div class="card-footer bg-transparent border-0 pt-0">
                                        <div class="d-grid">
                                            <a href="{{ route('data-umkm.map') }}?umkm={{ $umkm->id_umkm }}"
                                                class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-external-link-alt me-1"></i>
                                                Lihat Detail
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="recommended-mobile-slider d-md-none" aria-label="Slider rekomendasi UMKM">
                        @foreach ($recommendedUmkms as $umkm)
                            <article class="recommended-mobile-item">
                                <div class="recommended-card card h-100 border-0 shadow-sm">
                                    <div class="card-img-container position-relative">
                                        <img src="{{ $umkm->foto_umkm_url }}" alt="Foto {{ $umkm->nama_umkm }}"
                                            class="recommended-image"
                                            onerror="this.onerror=null;this.src='{{ $defaultUmkmImage }}';">
                                        <div class="recommended-badge">
                                            <i class="fas fa-crown text-warning"></i>
                                        </div>
                                    </div>

                                    <div class="card-body d-flex flex-column text-center p-2">
                                        <h6 class="card-title mb-1">{{ Str::limit($umkm->nama_umkm, 18) }}</h6>

                                        <div class="mb-2">
                                            <small class="badge bg-primary">
                                                {{ Str::limit(optional($umkm->kategori)->nama_kategori ?? 'Kategori', 12) }}
                                            </small>
                                        </div>

                                        @php
                                            $avgRating = $umkm->rating->avg('nilai_rating') ?? 0;
                                        @endphp
                                        <div class="mb-2">
                                            <small class="text-muted">{{ number_format($avgRating, 1) }} ★</small>
                                        </div>
                                    </div>

                                    <div class="card-footer bg-transparent border-0 pt-0 p-2">
                                        <a href="{{ route('data-umkm.map') }}?umkm={{ $umkm->id_umkm }}"
                                            class="btn btn-outline-primary btn-sm w-100">
                                            Detail
                                        </a>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        <!-- UMKM Grid Section -->
        <section class="umkm-section py-5">
            <div class="container">
                @php
                    $hasFilters =
                        request()->filled('search') ||
                        request()->filled('id_kategori') ||
                        request()->filled('id_kelompok');
                @endphp
                @if ($umkms->isEmpty())
                    <div class="text-center py-5">
                        <div class="empty-state">
                            <i class="fas fa-store fa-5x text-muted mb-4"></i>
                            <h3 class="text-muted mb-3">
                                @if ($hasFilters)
                                    Tidak ada UMKM ditemukan
                                @else
                                    Belum ada data UMKM
                                @endif
                            </h3>
                            <p class="text-muted mb-4">
                                @if ($hasFilters)
                                    Coba ubah filter atau kata kunci pencarian
                                @else
                                    Data UMKM sedang dipersiapkan
                                @endif
                            </p>
                            @if ($hasFilters)
                                <a href="{{ route('landing') }}" class="btn btn-primary">
                                    <i class="fas fa-list me-2"></i>Lihat Semua UMKM
                                </a>
                            @endif
                        </div>
                    </div>
                @else
                    <!-- Results Count -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <h2 class="h4 mb-0">
                                    @if ($hasFilters)
                                        Hasil Filter ({{ $umkms->total() }} UMKM ditemukan)
                                    @else
                                        Semua UMKM ({{ $umkms->total() }})
                                    @endif
                                </h2>
                                <div class="d-flex align-items-center">
                                    <small class="text-muted me-3">
                                        Halaman {{ $umkms->currentPage() }} dari {{ $umkms->lastPage() }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- UMKM Grid -->
                    <div class="row g-4">
                        @foreach ($umkms as $umkm)
                            <div class="col-xl-3 col-lg-4 col-md-6 col-6">
                                <div class="umkm-card card h-100 border-0 shadow-sm">
                                    <!-- Image Placeholder -->
                                    <div class="card-img-container">
                                        <img src="{{ $umkm->foto_umkm_url }}" alt="Foto {{ $umkm->nama_umkm }}"
                                            class="umkm-image"
                                            onerror="this.onerror=null;this.src='{{ $defaultUmkmImage }}';">
                                        <div class="umkm-overlay">
                                            <a href="{{ route('data-umkm.map') }}?umkm={{ $umkm->id_umkm }}"
                                                class="btn btn-primary btn-sm">
                                                <i class="fas fa-map-marked-alt me-1"></i>
                                                Lihat di Map
                                            </a>
                                        </div>
                                    </div>

                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title mb-2">{{ $umkm->nama_umkm }}</h5>

                                        <!-- Category Badge -->
                                        <div class="mb-2">
                                            <span class="badge bg-primary">
                                                <i class="fas fa-tag me-1"></i>
                                                {{ optional($umkm->kategori)->nama_kategori ?? 'Tidak dikategorikan' }}
                                            </span>
                                        </div>

                                        <!-- Operating Hours -->
                                        <div class="mb-2">
                                            <small class="text-muted">
                                                <i class="fas fa-clock me-1"></i>
                                                {{ $umkm->jam_buka }}
                                            </small>
                                        </div>

                                        <!-- Address -->
                                        <div class="mb-3">
                                            <small class="text-muted">
                                                <i class="fas fa-map-marker-alt me-1"></i>
                                                {{ Str::limit($umkm->alamat_lengkap, 60) }}
                                            </small>
                                        </div>

                                        <!-- Rating -->
                                        <div class="mt-auto">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="rating">
                                                    @php
                                                        $avgRating = $umkm->rating->avg('nilai_rating') ?? 0;
                                                        $ratingCount = $umkm->rating->count();
                                                    @endphp
                                                    <div class="stars">
                                                        @for ($i = 1; $i <= 5; $i++)
                                                            @if ($i <= floor($avgRating))
                                                                <i class="fas fa-star text-warning"></i>
                                                            @elseif($i - 0.5 <= $avgRating)
                                                                <i class="fas fa-star-half-alt text-warning"></i>
                                                            @else
                                                                <i class="far fa-star text-warning"></i>
                                                            @endif
                                                        @endfor
                                                    </div>
                                                    <small class="text-muted ms-1">
                                                        ({{ number_format($avgRating, 1) }} • {{ $ratingCount }} ulasan)
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Card Footer with Action -->
                                    <div class="card-footer bg-transparent border-0 pt-0">
                                        <div class="d-grid">
                                            <a href="{{ route('data-umkm.map') }}?umkm={{ $umkm->id_umkm }}"
                                                class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-external-link-alt me-1"></i>
                                                Detail & Lokasi
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="d-flex flex-column flex-md-row justify-content-center align-items-center mt-5">
                        <div class="text-muted mb-2 mb-md-0">
                            Menampilkan {{ $umkms->firstItem() ?? 0 }} - {{ $umkms->lastItem() ?? 0 }} dari
                            {{ $umkms->total() }} UMKM
                        </div>
                        <div>
                            {{-- Panggil custom view yang dibuat di Langkah 1 --}}
                            {{ $umkms->appends(request()->query())->links('layouts.custom') }}
                        </div>
                    </div>
                @endif
            </div>
        </section>
    </div>

    @include('partials.umkm-submission-modal')
@endsection

@push('styles')
    @vite('resources/css/landing.css')
@endpush

@push('scripts')
    @vite('resources/js/landing.js')
@endpush
