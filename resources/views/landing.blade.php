@extends('layouts.app')

@section('title', 'UMKM SEKITAR UPI - Temukan Makanan dan Minuman')

@section('content')
    @php
        $defaultUmkmImage = asset('images/default-umkm.svg');
        $preferredCategoryIds = $preferredCategoryIds ?? [];
    @endphp
    <div class="container-fluid p-0 landing-page">
        <!-- Hero Section Neo-Brutalism -->
        <section class="neo-hero-section">
            <div class="container hero-inner">
                <div class="row justify-content-center">
                    <div class="col-12 col-md-10 col-lg-8">

                        <!-- Panel Utama -->
                        <div class="neo-hero-panel text-center">

                            <!-- Badge -->
                            <div class="neo-hero-badge">
                                <i class="fas fa-utensils me-1"></i>
                                UMKM Kuliner
                            </div>

                            <!-- Judul dengan efek 3D -->
                            <h1 class="neo-hero-title">Temukan UMKM Terdekat</h1>

                            <!-- Subjudul -->
                            <p class="neo-hero-subtitle">
                                Jelajahi UMKM pilihan di sekitar Anda dengan rekomendasi yang relevan dan lokasi yang
                                akurat.
                            </p>

                            <!-- Tombol Aksi -->
                            <div class="neo-hero-actions">
                                <a href="{{ route('data-umkm.map') }}" class="neo-hero-btn-primary">
                                    <i class="fas fa-map-marked-alt"></i>
                                    Lihat Semua di Peta
                                </a>
                                <button type="button" class="neo-hero-btn-ghost" data-bs-toggle="modal"
                                    data-bs-target="#umkmSubmissionModal">
                                    <i class="fas fa-plus-circle"></i>
                                    Daftarkan UMKM
                                </button>
                            </div>

                            <!-- Chips Info -->
                            <div class="neo-hero-chips">
                                <span class="neo-hero-chip"><i class="fas fa-map-marker-alt text-danger me-1"></i> Lokasi
                                    akurat</span>
                                <span class="neo-hero-chip"><i class="fas fa-star text-warning me-1"></i> Rekomendasi
                                    personal</span>
                                <span class="neo-hero-chip"><i class="fas fa-bolt text-primary me-1"></i> Update
                                    cepat</span>
                            </div>

                        </div>

                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- top 10 UMKM Section -->
    <section class="top-umkm-section py-5">
        <div class="container">
            <div class="row mb-4">
                <div class="col-12 text-center">
                    <h2 class="h3 mb-3 text-dark">
                        <i class="fas fa-trophy text-warning me-2"></i>
                        Top 10 UMKM Terpopuler
                    </h2>
                    <p class="text-muted">Papan peringkat UMKM yang paling banyak dilihat oleh pelanggan</p>
                </div>
            </div>

            @if ($topClicks->isEmpty())
                <div class="text-muted text-center py-5 empty-state">Belum ada data klik UMKM saat ini.</div>
            @else
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <div class="leaderboard-wrapper">
                            @foreach ($topClicks as $umkm)
                                <div class="leaderboard-row">
                                    <div class="lb-rank">
                                        {{ $loop->iteration }}
                                    </div>

                                    <img src="{{ $umkm->foto_umkm_url }}" alt="Foto {{ $umkm->nama_umkm }}" class="lb-image"
                                        onerror="this.onerror=null;this.src='{{ $defaultUmkmImage }}';">

                                    <div class="lb-details">
                                        <div class="lb-name">
                                            {{ Str::limit($umkm->nama_umkm, 35) }}
                                        </div>
                                        <div class="lb-meta">
                                            <span class="badge bg-light text-dark shadow-sm">
                                                <i class="fas fa-tag me-1"></i>
                                                {{ optional($umkm->kategori)->nama_kategori ?? 'Umum' }}
                                            </span>

                                            @php
                                                $avgRating = $umkm->rating->avg('nilai_rating') ?? 0;
                                                $ratingCount = $umkm->rating->count();
                                            @endphp

                                            <div class="d-flex align-items-center">
                                                <div class="lb-stars me-1">
                                                    @for ($i = 1; $i <= 5; $i++)
                                                        @if ($i <= floor($avgRating))
                                                            <i class="fas fa-star"></i>
                                                        @elseif($i - 0.5 <= $avgRating)
                                                            <i class="fas fa-star-half-alt"></i>
                                                        @else
                                                            <i class="far fa-star"></i>
                                                        @endif
                                                    @endfor
                                                </div>
                                                <span class="fw-bold ms-1"
                                                    style="font-size: 0.65rem;">{{ number_format($avgRating, 1) }}</span>
                                                <span class="ms-1"
                                                    style="font-size: 0.65rem; opacity: 0.8;">({{ $ratingCount }})
                                                    ulasan</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="lb-score-action">
                                        <div class="lb-score">
                                            <i class="fas fa-fire me-1"></i>
                                            @if ((int) $umkm->total_klik >= 1000000)
                                                {{ floor((int) $umkm->total_klik / 1000000) }}jt+ Dilihat
                                            @else
                                                {{ number_format((int) $umkm->total_klik, 0, ',', '.') }}x Dilihat
                                            @endif
                                        </div>
                                        <button type="button" class="btn btn-light btn-sm fw-bold shadow-sm"
                                            data-umkm-detail data-umkm-id="{{ $umkm->id_umkm }}"
                                            data-detail-url="{{ route('umkm.detail', $umkm->id_umkm) }}"
                                            data-track-url="{{ route('umkm.track', $umkm->id_umkm) }}">
                                            Detail <i class="fas fa-chevron-right ms-1" style="font-size: 0.7rem;"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>

    <!-- Recommended UMKM Section -->
    @if ($recommendedUmkms->isNotEmpty())
        <section class="recommended-section py-5">
            <div class="container">
                <!-- Header Section -->
                <div class="row mb-4">
                    <div class="col-12 text-center">
                        <h2 class="h3 mb-3 fw-bold text-dark text-uppercase" style="-webkit-text-stroke: 1px black;">
                            <i class="fas fa-star text-warning me-2" style="-webkit-text-stroke: 2px black;"></i>
                            Rekomendasi Terbaik
                        </h2>
                        <p class="text-muted fw-bold">UMKM pilihan dengan rating tertinggi dari pelanggan</p>
                    </div>
                </div>

                <!-- DESKTOP VIEW (Grid) -->
                <div class="row g-4 d-none d-md-flex">
                    @foreach ($recommendedUmkms as $umkm)
                        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                            <div class="neo-card-rekomen h-100">
                                <!-- Image Container -->
                                <div class="position-relative">
                                    <img src="{{ $umkm->foto_umkm_url }}" alt="Foto {{ $umkm->nama_umkm }}"
                                        class="neo-img-rekomen"
                                        onerror="this.onerror=null;this.src='{{ $defaultUmkmImage }}';">

                                    <!-- Badge Mahkota -->
                                    <span class="position-absolute top-0 end-0 m-2"
                                        style="background: #ffde59; border: 2px solid #000; padding: 2px 6px; box-shadow: 2px 2px 0 #000;">
                                        <i class="fas fa-crown text-dark"></i>
                                    </span>
                                </div>

                                <!-- Card Body -->
                                <div class="p-2 p-md-3 d-flex flex-column flex-grow-1 text-center bg-white">
                                    <div class="mb-2">
                                        <h6 class="fw-bold text-dark m-0 text-truncate" style="font-size: 0.95rem;">
                                            {{ $umkm->nama_umkm }}</h6>
                                    </div>

                                    <!-- Category Badge dengan Wrapper Anti-Keluar -->
                                    <div class="badge-rekomen-wrapper mb-3">
                                        <span class="neo-badge-rekomen-dekstop">
                                            <i class="fas fa-tag me-1"></i>
                                            {{ optional($umkm->kategori)->nama_kategori ?? 'Umum' }}
                                        </span>
                                    </div>

                                    <!-- Rating -->
                                    <div class="mt-auto mb-3">
                                        @php
                                            $avgRating = $umkm->rating->avg('nilai_rating') ?? 0;
                                            $ratingCount = $umkm->rating->count();
                                        @endphp
                                        <div class="d-flex flex-column justify-content-center align-items-center">
                                            <div class="fw-bold text-dark mb-1" style="font-size: 0.9rem;">
                                                <i class="fas fa-star text-warning me-1"
                                                    style="-webkit-text-stroke: 1px black;"></i>
                                                {{ number_format($avgRating, 1) }}
                                            </div>
                                            <small class="text-muted fw-bold" style="font-size: 0.75rem;">
                                                ({{ $ratingCount }} ulasan)
                                            </small>
                                        </div>
                                    </div>

                                    <!-- Button Detail -->
                                    <button type="button" class="neo-button mt-auto"
                                        style="padding: 6px; font-size: 0.8rem;" data-umkm-detail
                                        data-umkm-id="{{ $umkm->id_umkm }}"
                                        data-detail-url="{{ route('umkm.detail', $umkm->id_umkm) }}"
                                        data-track-url="{{ route('umkm.track', $umkm->id_umkm) }}">
                                        <span>Detail</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- MOBILE VIEW (Slider) -->
                <div class="recommended-mobile-slider d-md-none" aria-label="Slider rekomendasi UMKM">
                    @foreach ($recommendedUmkms as $umkm)
                        <article class="recommended-mobile-item">
                            <div class="neo-card-rekomen h-100">
                                <!-- Image Container -->
                                <div class="position-relative">
                                    <img src="{{ $umkm->foto_umkm_url }}" alt="Foto {{ $umkm->nama_umkm }}"
                                        class="neo-img-rekomen"
                                        onerror="this.onerror=null;this.src='{{ $defaultUmkmImage }}';">

                                    <span class="position-absolute top-0 end-0 m-1"
                                        style="background: #ffde59; border: 2px solid #000; padding: 2px 6px; box-shadow: 2px 2px 0 #000; font-size: 0.7rem;">
                                        <i class="fas fa-crown text-dark"></i>
                                    </span>
                                </div>

                                <!-- Card Body -->
                                <div class="p-2 d-flex flex-column flex-grow-1 text-center bg-white">
                                    <div class="mb-2">
                                        <h6 class="fw-bold text-dark m-0 text-truncate" style="font-size: 0.9rem;">
                                            {{ $umkm->nama_umkm }}</h6>
                                    </div>

                                    <!-- Category Badge dengan Wrapper Anti-Keluar -->
                                    <div class="badge-rekomen-wrapper mb-2">
                                        <span class="neo-badge-rekomen">
                                            <i class="fas fa-tag me-1"></i>
                                            {{ optional($umkm->kategori)->nama_kategori ?? 'Umum' }}
                                        </span>
                                    </div>

                                    @php
                                        $avgRating = $umkm->rating->avg('nilai_rating') ?? 0;
                                        $ratingCount = $umkm->rating->count();
                                    @endphp

                                    <div class="mt-auto mb-2 d-flex flex-column justify-content-center align-items-center">
                                        <div class="fw-bold text-dark mb-1" style="font-size: 0.85rem;">
                                            <i class="fas fa-star text-warning me-1"
                                                style="-webkit-text-stroke: 1px black;"></i>
                                            {{ number_format($avgRating, 1) }} ({{ $ratingCount }} ulasan)
                                        </div>
                                    </div>

                                    <button type="button" class="neo-button mt-auto"
                                        style="padding: 6px; font-size: 0.75rem;" data-umkm-detail
                                        data-umkm-id="{{ $umkm->id_umkm }}"
                                        data-detail-url="{{ route('umkm.detail', $umkm->id_umkm) }}"
                                        data-track-url="{{ route('umkm.track', $umkm->id_umkm) }}">
                                        <span>Detail</span>
                                    </button>
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
                    request()->filled('id_kelompok') ||
                    request()->filled('min_rating');
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
                <div class="row g-3 g-md-4">
                    @foreach ($umkms as $umkm)
                        @php
                            $isRecommended = (bool) ($umkm->is_recommended ?? false);
                        @endphp
                        <div class="col-xl-3 col-lg-4 col-md-6 col-6">

                            <div class="neo-card h-100">

                                <!-- Image Container -->
                                <div class="neo-img-container">
                                    <!-- Hapus inline style height:160px dan ganti dengan class neo-img -->
                                    <img src="{{ $umkm->foto_umkm_url }}" alt="Foto {{ $umkm->nama_umkm }}"
                                        class="neo-img w-100 object-fit-cover"
                                        onerror="this.onerror=null;this.src='{{ $defaultUmkmImage }}';">

                                    @if ($isRecommended)
                                        <span class="badge neo-badge rekomen position-absolute top-0 end-0 m-1 m-md-2">
                                            ⭐ <span class="d-none d-md-inline">Rekomendasi</span>
                                        </span>
                                    @endif
                                </div>

                                <!-- Card Body (Padding responsif: p-2 di HP, p-3 di PC) -->
                                <div class="p-2 p-md-3 d-flex flex-column flex-grow-1 bg-white">

                                    <!-- Title -->
                                    <div class="mb-md-2">
                                        <h5 class="neo-title m-0">{{ $umkm->nama_umkm }}</h5>
                                    </div>

                                    <!-- Category Badge -->
                                    <div class="mb-1 mb-md-3 text-truncate">
                                        <span class="badge neo-badge">
                                            <i class="fas fa-tag me-1"></i>
                                            {{ optional($umkm->kategori)->nama_kategori ?? 'Umum' }}
                                        </span>
                                    </div>

                                    <!-- Rating (Disusun agar tidak turun baris berantakan) -->
                                    <div class="mt-auto mb-2 mb-md-3">
                                        @php
                                            $avgRating = $umkm->rating->avg('nilai_rating') ?? 0;
                                            $ratingCount = $umkm->rating->count();
                                        @endphp
                                        <div class="d-flex align-items-center fw-bold text-dark"
                                            style="font-size: 0.8rem;">
                                            <i class="fas fa-star text-warning me-1"
                                                style="-webkit-text-stroke: 1px black;"></i>
                                            <span style="font-size: 0.9rem;">{{ number_format($avgRating, 1) }}</span>
                                            <!-- Kata "ulasan" disembunyikan di HP agar muat (hanya angka) -->
                                            <span class="text-muted fw-normal ms-1">
                                                ({{ $ratingCount }}<span> ulasan</span>)
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Neo-Brutalist Button -->
                                    <button type="button" class="neo-button" data-umkm-detail
                                        data-umkm-id="{{ $umkm->id_umkm }}"
                                        data-detail-url="{{ route('umkm.detail', $umkm->id_umkm) }}"
                                        data-track-url="{{ route('umkm.track', $umkm->id_umkm) }}">
                                        <!-- Kata "& Lokasi" disembunyikan di HP -->
                                        <span>Detail<span class="d-none d-md-inline"> & Lokasi</span></span>
                                    </button>

                                </div>
                            </div>

                        </div>
                    @endforeach
                </div>
                <!-- Pagination -->
                <div class="d-flex flex-column justify-content-center align-items-center mt-3 mb-3">
                    <div class="text-muted mb-2">
                        Menampilkan {{ $umkms->firstItem() ?? 0 }} - {{ $umkms->lastItem() ?? 0 }} dari
                        {{ $umkms->total() }} UMKM
                    </div>
                    <div>
                        {{ $umkms->appends(request()->query())->links('layouts.custom') }}
                    </div>
                </div>
            @endif
        </div>
    </section>
    </div>

    <!-- UMKM Detail Modal (Landing) -->
    <div class="modal fade" id="umkmDetailModal" tabindex="-1" aria-labelledby="umkmDetailModalLabel"
        aria-hidden="true" data-map-url="{{ route('data-umkm.map') }}" data-default-image="{{ $defaultUmkmImage }}">
        <div class="modal-dialog modal-dialog-centered modal-lg">

            <!-- Menggunakan class .neo-modal -->
            <div class="modal-content neo-modal border-0">

                <!-- Header Modal -->
                <div class="modal-header neo-modal-header border-bottom-0">
                    <h5 class="modal-title neo-modal-title" id="umkmDetailModalLabel">
                        <i class="fas fa-store me-2 text-dark"></i>Detail UMKM
                    </h5>
                    <!-- Tombol close bergaya neo -->
                    <button type="button" class="btn-close neo-btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <!-- Body Modal -->
                <div class="modal-body p-4">
                    <div id="umkmDetailLoading" class="text-center py-4">
                        <div class="spinner-border text-dark" role="status"></div>
                        <div class="small fw-bold text-dark mt-2 text-uppercase">Memuat detail...</div>
                    </div>

                    <!-- Alert bergaya brutalist -->
                    <div id="umkmDetailError" class="alert d-none"
                        style="background: #ff3838; color: #fff; border: 3px solid #000; border-radius: 0; font-weight: bold; box-shadow: 4px 4px 0 #000;"
                        role="alert">
                        Gagal memuat detail UMKM. Silakan coba lagi.
                    </div>

                    <div id="umkmDetailContent" class="d-none">
                        <div class="d-flex flex-column flex-md-row gap-4">
                            <!-- Kolom Gambar -->
                            <div class="flex-shrink-0 text-center text-md-start">
                                <img id="umkmDetailImage" src="{{ $defaultUmkmImage }}" alt="Foto UMKM"
                                    class="umkm-detail-modal-image">
                            </div>

                            <!-- Kolom Informasi -->
                            <div class="flex-grow-1 text-dark">
                                <h4 id="umkmDetailName" class="mb-3 fw-bold text-uppercase"
                                    style="border-bottom: 3px solid #000; padding-bottom: 8px;">-</h4>

                                <div class="mb-3 d-flex align-items-center">
                                    <span class="badge"
                                        style="background: #e9ecef; border: 2px solid #000; color: #000; font-size: 0.85rem; border-radius: 0; box-shadow: 2px 2px 0 #000;">
                                        <i class="fas fa-tag me-1"></i><span id="umkmDetailCategory">-</span>
                                    </span>
                                </div>

                                <p class="mb-2 fw-bold" style="font-size: 0.95rem;">
                                    <i class="fas fa-clock me-2" style="width: 20px;"></i><span
                                        id="umkmDetailHours">-</span>
                                </p>

                                <p class="mb-0 fw-bold" style="font-size: 0.95rem;">
                                    <i class="fas fa-map-marker-alt me-2" style="width: 20px;"></i><span
                                        id="umkmDetailAddress">-</span>
                                </p>

                                <div class="mt-4 pt-3" style="border-top: 2px dashed #000;">
                                    <div class="rating umkm-rating-block">
                                        <!-- Rating Bintang -->
                                        <div id="umkmDetailRatingStars" class="stars text-warning"
                                            style="font-size: 1rem; -webkit-text-stroke: 1px black;">
                                        </div>
                                        <small id="umkmDetailRatingText"
                                            class="text-dark fw-bold umkm-rating-meta mt-1 d-block"
                                            style="font-size: 0.85rem;">
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Modal -->
                <div
                    class="modal-footer neo-modal-footer border-top-0 justify-content-between justify-content-md-end gap-2">
                    <button type="button" class="neo-btn-outline w-100 w-md-auto m-0"
                        data-bs-dismiss="modal">Tutup</button>
                    <a id="umkmDetailMapLink" href="{{ route('data-umkm.map') }}"
                        class="neo-btn-solid w-100 w-md-auto text-decoration-none text-center m-0">
                        <i class="fas fa-map-marked-alt me-1"></i> Lihat Peta
                    </a>
                </div>

            </div>
        </div>
    </div>

    <!-- Preference Modal (Content-Based Filtering) -->
    <div class="modal fade" id="preferenceModal" tabindex="-1" aria-labelledby="preferenceModalLabel"
        aria-hidden="true" data-auto-show="{{ $shouldShowPreferenceModal ? 'true' : 'false' }}"
        data-auto-show-delay="10000" data-max-selection="3" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">

            <form id="preferenceForm" class="modal-content neo-pref-modal border-0"
                action="{{ route('landing.preference.store') }}" method="POST">
                @csrf

                <!-- Header -->
                <div class="modal-header neo-pref-header border-bottom-0">
                    <h5 class="modal-title neo-pref-title" id="preferenceModalLabel">
                        <i class="fas fa-bullseye me-2 text-dark"></i> Kamu suka apa?
                    </h5>
                </div>

                <!-- Body -->
                <div class="modal-body p-4">
                    <p class="text-dark fw-bold mb-4"
                        style="font-size: 0.95rem; border-left: 4px solid #5ad641; padding-left: 10px;">
                        Pilih maksimal 3 kategori kuliner yang paling kamu sukai.
                    </p>

                    <!-- List Pilihan -->
                    <div class="preference-category-list mb-2">
                        @foreach ($kategoriList as $kategori)
                            <label class="neo-pref-option">
                                <input type="checkbox" name="kategori_ids[]" value="{{ $kategori->id_kategori }}"
                                    {{ in_array((int) $kategori->id_kategori, $preferredCategoryIds, true) ? 'checked' : '' }}>

                                <div class="neo-pref-card">
                                    <span class="fw-bold text-uppercase" style="font-size: 0.9rem;">
                                        {{ $kategori->nama_kategori }}
                                    </span>
                                    <small class="neo-muted-text text-muted mt-1"
                                        style="font-size: 0.75rem; font-weight: 700;">
                                        {{ optional($kategori->kelompok)->nama_kelompok ?? 'Tanpa Kelompok' }}
                                    </small>
                                </div>
                            </label>
                        @endforeach
                    </div>

                    <!-- Counter Batas Pilihan -->
                    <div class="d-flex justify-content-between align-items-center mt-4 pt-3"
                        style="border-top: 3px dashed #000;">
                        <small class="fw-bold text-dark text-uppercase" style="font-size: 0.8rem;">Batas Pilihan</small>
                        <small class="fw-bold text-dark px-2 py-1" id="preferenceCount"
                            style="background: #e0e0e0; border: 2px solid #000; box-shadow: 2px 2px 0 #000;">
                            0/3 Dipilih
                        </small>
                    </div>

                    <!-- Pesan Error -->
                    <div id="preferenceError" class="d-none mt-3 p-2 text-white fw-bold text-center text-uppercase"
                        style="background: #ff3838; border: 3px solid #000; font-size: 0.8rem; box-shadow: 3px 3px 0 #000;">
                    </div>
                </div>

                <!-- Footer -->
                <div class="modal-footer neo-pref-footer border-top-0">
                    <button type="submit" class="neo-pref-btn">
                        Lanjut <i class="fas fa-arrow-right ms-2"></i>
                    </button>
                </div>
            </form>

        </div>
    </div>

    @include('partials.umkm-submission-modal')
@endsection

@push('styles')
    @vite('resources/css/landing.css')
@endpush

@push('scripts')
    @vite('resources/js/landing.js')
@endpush
