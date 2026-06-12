@extends('layouts.app')

@section('title', 'UMKM SEKITAR UPI - Temukan Makanan dan Minuman')

@section('content')
    @php
        $defaultUmkmImage = asset('images/default-umkm.svg');
        $preferredCategoryIds = $preferredCategoryIds ?? [];
    @endphp
    <div class="container-fluid p-0 landing-page">
        <!-- Hero Section -->
        <section class="hero-section">
            <div class="container hero-inner">
                <div class="row justify-content-center">
                    <div class="col-12 col-md-10 col-lg-8">
                        <div class="hero-panel text-center">
                            <div class="hero-badge">
                                <i class="fas fa-utensils"></i>
                                UMKM Kuliner
                            </div>
                            <h1 class="hero-title">Temukan UMKM Terdekat</h1>
                            <p class="hero-subtitle">
                                Jelajahi UMKM pilihan di sekitar Anda dengan rekomendasi yang relevan dan lokasi yang akurat.
                            </p>
                            <div class="hero-actions">
                                <a href="{{ route('data-umkm.map') }}" class="btn btn-hero-primary">
                                    <i class="fas fa-map-marked-alt"></i>
                                    Lihat Semua di Peta
                                </a>
                                <button type="button" class="btn btn-hero-ghost" data-bs-toggle="modal" data-bs-target="#umkmSubmissionModal">
                                    <i class="fas fa-plus-circle"></i>
                                    Daftarkan UMKM
                                </button>
                            </div>
                            <div class="hero-chips">
                                <span class="hero-chip"><i class="fas fa-map-marker-alt"></i> Lokasi akurat</span>
                                <span class="hero-chip"><i class="fas fa-star"></i> Rekomendasi personal</span>
                                <span class="hero-chip"><i class="fas fa-bolt"></i> Update cepat</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Recommended UMKM Section -->
        @if ($recommendedUmkms->isNotEmpty())
            <section class="recommended-section py-5">
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
                                            <button type="button"
                                                class="btn btn-outline-primary btn-sm"
                                                data-umkm-detail
                                                data-umkm-id="{{ $umkm->id_umkm }}"
                                                data-detail-url="{{ route('umkm.detail', $umkm->id_umkm) }}"
                                                data-track-url="{{ route('umkm.track', $umkm->id_umkm) }}">
                                                <i class="fas fa-external-link-alt me-1"></i>
                                                Lihat Detail
                                            </button>
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
                                        <button type="button"
                                            class="btn btn-outline-primary btn-sm w-100"
                                            data-umkm-detail
                                            data-umkm-id="{{ $umkm->id_umkm }}"
                                            data-detail-url="{{ route('umkm.detail', $umkm->id_umkm) }}"
                                            data-track-url="{{ route('umkm.track', $umkm->id_umkm) }}">
                                            Detail
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
                    <div class="row g-4">
                        @foreach ($umkms as $umkm)
                            @php
                                $isRecommended = (bool) ($umkm->is_recommended ?? false);
                            @endphp
                            <div class="col-xl-3 col-lg-4 col-md-6 col-6">
                                <div class="umkm-card card h-100 border-0 shadow-sm">
                                    <!-- Image Placeholder -->
                                    <div class="card-img-container">
                                        <img src="{{ $umkm->foto_umkm_url }}" alt="Foto {{ $umkm->nama_umkm }}"
                                            class="umkm-image"
                                            onerror="this.onerror=null;this.src='{{ $defaultUmkmImage }}';">
                                        @if ($isRecommended)
                                            <span class="preference-badge">⭐ Rekomendasi</span>
                                        @endif
                                        <div class="umkm-overlay">
                                            <button type="button"
                                                class="btn btn-primary btn-sm"
                                                data-umkm-detail
                                                data-umkm-id="{{ $umkm->id_umkm }}"
                                                data-detail-url="{{ route('umkm.detail', $umkm->id_umkm) }}"
                                                data-track-url="{{ route('umkm.track', $umkm->id_umkm) }}">
                                                <i class="fas fa-map-marked-alt me-1"></i>
                                                Lihat Detail & Lokasi
                                            </button>
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
                                            <button type="button"
                                                class="btn btn-outline-primary btn-sm"
                                                data-umkm-detail
                                                data-umkm-id="{{ $umkm->id_umkm }}"
                                                data-detail-url="{{ route('umkm.detail', $umkm->id_umkm) }}"
                                                data-track-url="{{ route('umkm.track', $umkm->id_umkm) }}">
                                                <i class="fas fa-external-link-alt me-1"></i>
                                                Detail & Lokasi
                                            </button>
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

    <!-- UMKM Detail Modal (Landing) -->
    <div class="modal fade" id="umkmDetailModal" tabindex="-1" aria-labelledby="umkmDetailModalLabel" aria-hidden="true"
        data-map-url="{{ route('data-umkm.map') }}" data-default-image="{{ $defaultUmkmImage }}">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="umkmDetailModalLabel">
                        <i class="fas fa-store me-2 text-primary"></i>Detail UMKM
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="umkmDetailLoading" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status"></div>
                        <div class="small text-muted mt-2">Memuat detail...</div>
                    </div>

                    <div id="umkmDetailError" class="alert alert-danger d-none" role="alert">
                        Gagal memuat detail UMKM. Silakan coba lagi.
                    </div>

                    <div id="umkmDetailContent" class="d-none">
                        <div class="d-flex flex-column flex-md-row gap-3">
                            <img id="umkmDetailImage" src="{{ $defaultUmkmImage }}" alt="Foto UMKM" class="umkm-detail-modal-image">
                            <div>
                                <h5 id="umkmDetailName" class="mb-2">-</h5>
                                <p class="mb-2">
                                    <i class="fas fa-tag me-2 text-primary"></i><span id="umkmDetailCategory">-</span>
                                </p>
                                <p class="mb-0">
                                    <i class="fas fa-clock me-2 text-primary"></i><span id="umkmDetailHours">-</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                    <a id="umkmDetailMapLink" href="{{ route('data-umkm.map') }}" class="btn btn-primary">
                        <i class="fas fa-map-marked-alt me-1"></i>Lihat Lokasi di Map
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Preference Modal (Content-Based Filtering) -->
    <div class="modal fade" id="preferenceModal" tabindex="-1" aria-labelledby="preferenceModalLabel" aria-hidden="true"
        data-auto-show="{{ $shouldShowPreferenceModal ? 'true' : 'false' }}" data-auto-show-delay="15000" data-max-selection="3" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <form id="preferenceForm" class="modal-content" action="{{ route('landing.preference.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="preferenceModalLabel">
                        <i class="fas fa-bullseye me-2 text-warning"></i>Kamu suka apa?
                    </h5>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3">Pilih maksimal 3 kategori kuliner yang paling kamu sukai.</p>

                    <div class="preference-category-list">
                        @foreach ($kategoriList as $kategori)
                            <label class="preference-option">
                                <span class="preference-option__row">
                                    <input type="checkbox" name="kategori_ids[]" value="{{ $kategori->id_kategori }}"
                                        {{ in_array((int) $kategori->id_kategori, $preferredCategoryIds, true) ? 'checked' : '' }}>
                                    <span class="fw-semibold">{{ $kategori->nama_kategori }}</span>
                                </span>
                                <small class="text-muted d-block">{{ optional($kategori->kelompok)->nama_kelompok ?? 'Tanpa Kelompok' }}</small>
                            </label>
                        @endforeach
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <small class="text-muted">Maksimal 3 kategori.</small>
                        <small class="text-muted" id="preferenceCount">0/3 dipilih</small>
                    </div>

                    <div id="preferenceError" class="text-danger small mt-2 d-none"></div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Lanjut</button>
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
