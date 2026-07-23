@extends('layouts.maplay')

@section('title', 'Peta UMKM' . ($selectedUmkm ? ' - ' . $selectedUmkm->nama_umkm : ''))

@section('contentmap')

    @if ($dataUmkms->isEmpty())
        <div class="alert alert-warning abs-top-left">Tidak ada data UMKM.</div>
    @endif

    <!-- Kontrol Peta Utama -->
    <div class="map-controls p-3" id="mapControls">
        <div class="map-search-row">
            <input type="text" id="mapSearchInput" class="form-control neo-input"
                placeholder="Cari nama UMKM atau alamat...">
            <button type="button" class="btn btn-warning border-dark neo-btn" id="mapSearchBtn" title="Cari UMKM">
                <i class="fas fa-search"></i>
            </button>
            <button type="button" class="btn btn-light border-dark neo-btn" id="toggleSearchFiltersBtn" title="Filter">
                <i class="fas fa-filter"></i>
            </button>
        </div>

        <!-- Dropdown Filter Pencarian -->
        <div id="searchFilterDropdown" class="search-filter-dropdown mt-3 d-none">
            <div class="map-category-chips mb-3" id="categoryChips"></div>
            <div class="d-flex align-items-center justify-content-start mt-2 gap-3">
                <!-- Tambahkan gap-3 untuk memberi jarak antar elemen -->
                <button type="button" class="btn btn-outline-dark neo-btn btn-sm" id="toggleMoreFiltersBtn">
                    <i class="fas fa-sliders-h me-1"></i>Filter Lainnya
                </button>
                <!-- Teks sekarang punya ruang dari tombol -->
                <small id="mapResultInfo" class="fw-bold text-dark m-0">Menampilkan UMKM</small>
            </div>
        </div>

        <!-- Desktop Filter Panel -->
        <div id="desktopFilterPanel" class="desktop-filter-panel mt-3 p-3 border-top border-3 border-dark d-none">
            <label class="neo-form-label mb-1">Kelompok UMKM</label>
            <select id="desktopGroupFilter" class="form-select neo-input mb-3">
                <option value="all">Semua Kelompok</option>
            </select>

            <label class="neo-form-label mb-1">Rating Minimal</label>
            <select id="desktopMinRating" class="form-select neo-input mb-3">
                <option value="0">Semua Rating</option>
                <option value="1">1.0 ke atas</option>
                <option value="2">2.0 ke atas</option>
                <option value="3">3.0 ke atas</option>
                <option value="4">4.0 ke atas</option>
                <option value="4.5">4.5 ke atas</option>
                <option value="5">5.0 ke atas</option>
            </select>

            <div class="form-check mb-3">
                <input class="form-check-input neo-input" type="checkbox" id="desktopOpenNow">
                <label class="form-check-label fw-bold" for="desktopOpenNow">Sedang Buka</label>
            </div>

            <div class="d-flex gap-2">
                <button type="button" class="btn btn-success neo-btn w-100" id="desktopApplyFilters">TERAPKAN</button>
                <button type="button" class="btn btn-secondary neo-btn w-100" id="desktopResetFilters">RESET</button>
            </div>
        </div>
    </div>

    <!-- Backdrop untuk Mobile Filter -->
    <div id="mobileFilterBackdrop" class="mobile-filter-backdrop d-none"></div>

    <!-- Mobile Filter Sheet -->
    <div id="mobileFilterSheet" class="mobile-filter-sheet d-none p-4">
        <div class="sheet-handle"></div>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 fw-black text-uppercase">Filter Lainnya</h6>
            <button type="button" class="neo-btn-square-close" id="closeMobileFilterSheet">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <label class="neo-form-label mb-1">Kelompok UMKM</label>
        <select id="mobileGroupFilter" class="form-select neo-input mb-3">
            <option value="all">Semua Kelompok</option>
        </select>

        <label class="neo-form-label mb-1">Rating Minimal</label>
        <select id="mobileMinRating" class="form-select neo-input mb-3">
            <option value="0">Semua Rating</option>
            <option value="1">1.0 ke atas</option>
            <option value="2">2.0 ke atas</option>
            <option value="3">3.0 ke atas</option>
            <option value="4">4.0 ke atas</option>
            <option value="4.5">4.5 ke atas</option>
            <option value="5">5.0 ke atas</option>
        </select>

        <div class="form-check mb-4">
            <input class="form-check-input neo-input" type="checkbox" id="mobileOpenNow">
            <label class="form-check-label fw-bold" for="mobileOpenNow">Sedang Buka</label>
        </div>

        <div class="d-flex gap-2">
            <button type="button" class="btn btn-success neo-btn w-100" id="mobileApplyFilters">TERAPKAN</button>
            <button type="button" class="btn btn-secondary neo-btn w-100" id="mobileResetFilters">RESET</button>
        </div>
    </div>

    @if ($selectedUmkm)
        @php
            $selectedUmkmRatings = $selectedUmkm->rating ?? collect();
            $avgRating = (float) ($selectedUmkmRatings->avg('nilai_rating') ?? 0);
            $ratingCount = (int) $selectedUmkmRatings->count();
            $deskripsi = $selectedUmkm->deskripsi ?? 'Tidak tersedia';
            $ulasanItems = $selectedUmkmRatings->sortByDesc('created_at')->values();
        @endphp
        <div id="umkm-detail-panel" class="umkm-detail-panel neo-detail-panel">
            <div class="detail-header neo-detail-header">
                <div>
                    <h4 class="mb-0 fw-bold text-uppercase">{{ $selectedUmkm->nama_umkm }}</h4>
                </div>
                <button type="button" class="btn btn-outline-dark border-0 p-1 custom-btn-close"
                    onclick="closeDetailPanel()" aria-label="Tutup detail">
                    <i class="fas fa-times fa-lg"></i>
                </button>
            </div>

            <div class="detail-content bg-white">
                <div class="detail-section neo-detail-section">
                    <img src="{{ $selectedUmkm->foto_umkm_url }}" class="neo-detail-photo lightbox-trigger"
                        alt="Foto {{ $selectedUmkm->nama_umkm }}" style="cursor: zoom-in;"
                        onclick="openImageLightbox('{{ $selectedUmkm->foto_umkm_url }}', 'Foto {{ addslashes($selectedUmkm->nama_umkm) }}')"
                        onerror="this.onerror=null;this.src='{{ asset('images/default-umkm.svg') }}';">
                </div>

                <div class="detail-section neo-detail-section">
                    <h6 class="fw-bold text-uppercase"><i class="fas fa-tag me-2"></i>Kategori</h6>
                    <span
                        class="badge neo-badge">{{ optional($selectedUmkm->kategori)->nama_kategori ?? 'Tidak dikategorikan' }}</span>
                </div>

                <div class="detail-section neo-detail-section">
                    <h6 class="fw-bold text-uppercase"><i class="fas fa-clock me-2"></i>Jam Buka</h6>
                    <p class="mb-0 fw-semibold">{{ $selectedUmkm->jam_buka ?? 'Tidak tersedia' }}</p>
                </div>

                <div class="detail-section neo-detail-section">
                    <h6 class="fw-bold text-uppercase"><i class="fas fa-map-marker-alt me-2"></i>Alamat Lengkap</h6>
                    <p class="mb-0 fw-semibold">{{ $selectedUmkm->alamat_lengkap ?? 'Tidak tersedia' }}</p>
                </div>

                <div class="detail-section neo-detail-section">
                    <h6 class="fw-bold text-uppercase"><i class="fas fa-phone me-2"></i>No Telfon</h6>
                    @if ($selectedUmkm->no_telfon)
                        <p class="mb-0 fw-semibold">
                            <a href="tel:{{ preg_replace('/\s+/', '', $selectedUmkm->no_telfon) }}"
                                class="text-dark">{{ $selectedUmkm->no_telfon }}</a>
                        </p>
                    @else
                        <p class="mb-0 fw-semibold">-</p>
                    @endif
                </div>

                <div class="detail-section neo-detail-section">
                    <h6 class="fw-bold text-uppercase"><i class="fas fa-star me-2"></i>Rating</h6>
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <div class="stars me-1">
                            @for ($i = 1; $i <= 5; $i++)
                                @if ($i <= floor($avgRating))
                                    <i class="fas fa-star text-warning" style="-webkit-text-stroke: 1px #000;"></i>
                                @elseif($i - 0.5 <= $avgRating)
                                    <i class="fas fa-star-half-alt text-warning"
                                        style="-webkit-text-stroke: 1px #000;"></i>
                                @else
                                    <i class="far fa-star text-warning" style="-webkit-text-stroke: 1px #000;"></i>
                                @endif
                            @endfor
                        </div>
                        <small class="text-dark fw-bold">({{ number_format($avgRating, 1) }} • {{ $ratingCount }}
                            ulasan)</small>
                    </div>

                    <button type="button" class="btn btn-link btn-sm p-0 mt-2 text-dark fw-bold"
                        onclick="toggleUlasan('ulasan-list-{{ $selectedUmkm->id_umkm }}', this)">
                        <i class="fas fa-comments me-1"></i>Lihat ulasan
                    </button>

                    <div id="ulasan-list-{{ $selectedUmkm->id_umkm }}" class="ulasan-list-container d-none mt-2">
                        @if ($ulasanItems->isNotEmpty())
                            <div class="d-grid gap-2">
                                @foreach ($ulasanItems as $ulasan)
                                    <div class="ulasan-item">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <strong>{{ $ulasan->nama_pengulas ?: 'Anonymous' }}</strong>
                                            <small
                                                class="text-muted">{{ optional($ulasan->created_at)->translatedFormat('d M Y') ?? '-' }}</small>
                                        </div>
                                        {{-- <div class="stars mb-1">{!! generateStars((int) $ulasan->nilai_rating) !!}</div> --}}
                                        <p class="mb-0">{{ $ulasan->komentar ?: 'Pengguna tidak menulis ulasan.' }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="mb-0 text-muted">Belum ada ulasan untuk UMKM ini.</p>
                        @endif
                    </div>
                </div>

                <div class="detail-section neo-detail-section">
                    <h6 class="fw-bold text-uppercase"><i class="fas fa-info-circle me-2"></i>Deskripsi</h6>
                    <p class="mb-0 fw-semibold">{{ $selectedUmkm->deskripsi ?? 'Tidak tersedia' }}</p>
                </div>

                <div class="detail-section neo-detail-section">
                    <h6 class="fw-bold text-uppercase"><i class="fas fa-utensils me-2"></i>MENU UMKM</h6>
                    @php
                        $menuItems = $selectedUmkm->menu->filter(fn($menu) => !$menu->is_foto_daftar_menu);
                        $menuGallery = $selectedUmkm->menu->filter(
                            fn($menu) => $menu->is_foto_daftar_menu && $menu->foto_menu && $menu->foto_menu !== '-',
                        );
                    @endphp
                    @if ($menuItems->isNotEmpty())
                        <div class="menu-list d-grid gap-2">
                            @foreach ($menuItems as $menu)
                                <div class="menu-item d-flex align-items-center gap-2 border-dark border-2>
                                    <img src="{{ $menu->foto_menu_url }}"
                                    alt="Foto {{ $menu->nama_menu }}" class="menu-thumb border-dark border-2"
                                    onclick="openImageLightbox('{{ $menu->foto_menu_url }}', 'Foto {{ addslashes($menu->nama_menu) }}')"
                                    onerror="this.onerror=null;this.src='{{ asset('images/default-menu.svg') }}';">
                                    <div class="grow">
                                        <div class="fw-bold text-dark">{{ $menu->nama_menu }}</div>
                                        <small class="text-muted">
                                            {{ (float) $menu->harga_menu > 0 ? 'Rp ' . number_format((float) $menu->harga_menu, 0, ',', '.') : '- (harga belum dimasukan)' }}
                                        </small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="mb-1 text-muted fw-bold">Belum ada data menu dengan nama dan harga.</p>
                    @endif

                    @if ($menuGallery->isNotEmpty())
                        <div class="mt-2">
                            <small class="text-dark fw-bold d-block mb-2 mt-3">Foto daftar menu *Harga sewaktu-waktu dapat
                                berubah</small>
                            <div class="menu-gallery d-flex flex-wrap gap-2">
                                @foreach ($menuGallery as $menuPhoto)
                                    <img src="{{ $menuPhoto->foto_menu_url }}"
                                        alt="Foto daftar menu {{ $selectedUmkm->nama_umkm }}"
                                        class="menu-gallery-thumb border-dark border-2"
                                        onclick="openImageLightbox('{{ $menuPhoto->foto_menu_url }}', 'Foto daftar menu {{ addslashes($selectedUmkm->nama_umkm) }}')"
                                        onerror="this.onerror=null;this.src='{{ asset('images/default-menu.svg') }}';">
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <button type="button" class="btn btn-outline-dark btn-sm mt-2 neo-btn"
                        onclick="openMenuSubmissionModal({{ $selectedUmkm->id_umkm }}, '{{ addslashes($selectedUmkm->nama_umkm) }}')">
                        <i class="fas fa-plus-circle me-1"></i>Ajukan Menu Baru
                    </button>
                </div>
            </div>

            <div class="detail-actions p-2">
                <div class="row g-2">
                    <div class="col-6">
                        <button type="button" class="btn btn-success btn-sm w-100 neo-btn"
                            onclick="openRatingModal({{ $selectedUmkm->id_umkm }}, '{{ addslashes($selectedUmkm->nama_umkm) }}')">
                            <i class="fas fa-star me-1"></i>Beri Rating
                        </button>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('landing') }}" class="btn btn-outline-dark btn-sm w-100 neo-btn">
                            <i class="fas fa-arrow-left me-1"></i>Kembali
                        </a>
                    </div>
                    @if ($selectedUmkm->lokasi)
                        <div class="col-12 mt-2">
                            <button type="button" class="btn btn-info btn-sm w-100 neo-btn"
                                onclick="startLiveTrackingTo({{ (float) $selectedUmkm->lokasi->latitude }}, {{ (float) $selectedUmkm->lokasi->longitude }}, '{{ addslashes($selectedUmkm->nama_umkm) }}')">
                                <i class="fas fa-route me-1"></i> Live Track
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <div id="globalStopTrackingWrapper" class="global-stop-tracking d-none">
        <div id="liveTrackFloatingStatus" class="live-track-floating d-none" aria-live="polite"></div>
        <button type="button" id="globalStopTrackingBtn" class="btn btn-danger btn-sm shadow">
            <i class="fas fa-stop-circle me-1"></i>Stop Live Tracking
        </button>
    </div>
@endsection

<!-- Rating Modal -->
<div class="modal fade" id="ratingModal" tabindex="-1" data-bs-backdrop="false" aria-labelledby="ratingModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">

        <form id="ratingForm" action="{{ route('rating.store') }}" method="POST"
            class="neo-submit-form modal-content neo-modal-container border-0">
            @csrf

            <input type="hidden" name="tipe_form" value="form_rating">

            <!-- HEADER KHAKI -->
            <div class="modal-header neo-modal-header-khaki border-bottom-0">
                <h5 class="modal-title" id="ratingModalLabel">
                    <i class="fas fa-star text-dark me-2"></i>Rating
                </h5>
                <button type="button" class="neo-btn-square-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- BODY -->
            <div class="modal-body p-4 bg-white">

                @if ($errors->any() && old('tipe_form') === 'form_rating')
                    <div class="neo-alert-danger p-3 mb-4">
                        <div class="text-uppercase mb-2"><i
                                class="fas fa-exclamation-triangle me-2"></i><strong>Terdapat Kesalahan:</strong></div>
                        <ul class="mb-0 ps-3" style="font-size: 0.9rem;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <input type="hidden" id="ratingUmkmId" name="id_umkm" value="{{ old('id_umkm') }}">

                <div class="mb-4">
                    <label for="namaPengulas" class="neo-form-label">
                        <i class="fas fa-user me-2"></i>Nama Anda <span class="text-danger">(Opsional)</span>
                    </label>
                    <input type="text" name="nama_pengulas"
                        class="form-control neo-input {{ auth()->check() ? 'readonly-input' : '' }}"
                        value="{{ old('nama_pengulas', auth()->user()?->name) }}" placeholder="Contoh: Adrian M"
                        {{ auth()->check() ? 'readonly' : '' }}>
                </div>

                <div class="mb-4">
                    <label class="neo-form-label">
                        <i class="fas fa-star me-2"></i>Rating <span class="text-danger fs-5">*</span>
                    </label>

                    <div class="rating-stars neo-box p-3 bg-white text-center">
                        <input type="hidden" id="nilaiRating" name="nilai_rating"
                            value="{{ old('nilai_rating', 0) }}" required>
                        <div class="stars-container d-flex justify-content-center gap-2 mb-2">
                            <i class="far fa-star star text-warning" data-rating="1"
                                style="-webkit-text-stroke: 2px #000; font-size: 2.2rem; cursor: pointer;"></i>
                            <i class="far fa-star star text-warning" data-rating="2"
                                style="-webkit-text-stroke: 2px #000; font-size: 2.2rem; cursor: pointer;"></i>
                            <i class="far fa-star star text-warning" data-rating="3"
                                style="-webkit-text-stroke: 2px #000; font-size: 2.2rem; cursor: pointer;"></i>
                            <i class="far fa-star star text-warning" data-rating="4"
                                style="-webkit-text-stroke: 2px #000; font-size: 2.2rem; cursor: pointer;"></i>
                            <i class="far fa-star star text-warning" data-rating="5"
                                style="-webkit-text-stroke: 2px #000; font-size: 2.2rem; cursor: pointer;"></i>
                        </div>
                        <small class="fw-bold text-uppercase fs-6" id="ratingText">Belum dipilih</small>
                    </div>

                    <!-- PERBAIKAN: Tampilkan error jika rating kosong (0) -->
                    @error('nilai_rating')
                        <div class="text-danger fw-bold mt-2 text-uppercase">
                            <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="komentar" class="neo-form-label">
                        <i class="fas fa-comment me-2"></i>Komentar <span
                            class="text-muted fw-normal text-lowercase">(Opsional)</span>
                    </label>
                    <textarea class="form-control neo-input" id="komentar" name="komentar" rows="3"
                        placeholder="Berikan komentar atau ulasan Anda...">{{ old('komentar') }}</textarea>

                    @error('komentar')
                        <div class="text-danger fw-bold mt-2 text-uppercase">
                            <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <!-- FOOTER -->
            <div class="modal-footer neo-modal-footer border-top-0 d-flex justify-content-between">
                <button type="button" class="neo-btn-outline m-0" data-bs-dismiss="modal">BATAL</button>
                <button type="submit" class="neo-btn-solid m-0">
                    <i class="fas fa-paper-plane me-2"></i>KIRIM PENGAJUAN
                </button>
            </div>

            {{-- <div class="modal-footer border-top-0 d-flex justify-content-between p-4 pt-0 bg-white">
                <button type="button" class="neo-btn-grey m-0" data-bs-dismiss="modal">BATAL</button>
                <button type="submit" class="neo-btn-green m-0">
                    <i class="fas fa-paper-plane me-2"></i>KIRIM RATING
                </button>
            </div> --}}

        </form>
    </div>
</div>

{{-- rating modal behavior moved to resources/js/refactor/map-modals.js --}}

<div class="modal fade" id="menuSubmissionModal" data-bs-backdrop="false" tabindex="-1" aria-labelledby="menuSubmissionModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">

        <form method="POST" action="{{ route('menu-submissions.store') }}"
            class="neo-submit-form modal-content neo-modal-container border-0" novalidate
            enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="tipe_form" value="form_menu">
            @php
                $oldMenuTargetName = null;
                if (old('id_umkm')) {
                    $oldTarget = $dataUmkms->firstWhere('id_umkm', (int) old('id_umkm'));
                    $oldMenuTargetName = optional($oldTarget)->nama_umkm;
                }
                $oldMenuNames = old('menu_nama', ['']);
                $oldMenuPrices = old('menu_harga', ['']);
                $maxMenuRows = max(count($oldMenuNames), count($oldMenuPrices), 1);
            @endphp

            <!-- HEADER KHAKI -->
            <div class="modal-header neo-modal-header-khaki border-bottom-0">
                <h5 class="modal-title" id="menuSubmissionModalLabel">
                    <i class="fas fa-bullhorn me-2"></i> Ajukan Menu Baru
                </h5>
                <button type="button" class="neo-btn-square-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- BODY -->
            <div class="modal-body p-4">

                @if ($errors->any() && old('tipe_form') === 'form_menu')
                    <div class="neo-alert-danger p-3 mb-4">
                        <div class="text-uppercase mb-2"><i
                                class="fas fa-exclamation-triangle me-2"></i><strong>Terdapat Kesalahan:</strong></div>
                        <ul class="mb-0 ps-3" style="font-size: 0.9rem;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <input type="hidden" id="menuSubmissionUmkmId" name="id_umkm" value="{{ old('id_umkm') }}">

                <div class="row g-4">
                    <div class="col-12">
                        <label class="neo-form-label">UMKM Tujuan</label>
                        <div id="menuSubmissionTargetName" class="form-control neo-input text-dark fw-bold"
                            style="background-color: #dcdcdc; cursor: not-allowed; border-style: dashed !important;">
                            {{ $oldMenuTargetName ?: '-' }}
                        </div>
                    </div>

                    <!-- Data Pengusul -->
                    <div class="col-md-6">
                        <label class="neo-form-label">
                            Nama Pengusul <span class="text-danger fs-5">*</span>
                        </label>

                        <input type="text" name="nama_pengusul"
                            class="form-control neo-input {{ auth()->check() ? 'readonly-input' : '' }}"
                            value="{{ old('nama_pengusul', auth()->user()?->name) }}" placeholder="Contoh: Adrian M"
                            required {{ auth()->check() ? 'readonly' : '' }}>
                        @error('nama_pengusul')
                            <div class="text-danger fw-bold mt-2 text-uppercase">
                                <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="neo-form-label">
                            Email Pengusul <span class="text-danger fs-5">*</span>
                        </label>

                        <input type="email" name="email_pengusul"
                            class="form-control neo-input {{ auth()->check() ? 'readonly-input' : '' }}"
                            value="{{ old('email_pengusul', auth()->user()?->email) }}"
                            placeholder="contoh: rian@email.com" required {{ auth()->check() ? 'readonly' : '' }}>
                        @error('email_pengusul')
                            <div class="text-danger fw-bold mt-2 text-uppercase">
                                <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <hr style="border-top: 3px dashed #000; opacity: 1;">
                    </div>

                    <label class="neo-form-label mb-0 fs-6">Isi salah satu, Daftar Menu atau Foto Daftar Menu</label>

                    <div class="col-12">
                        <hr style="border-top: 3px dashed #000; opacity: 1;">
                    </div>

                    @error('menu_kosong')
                        <div class="text-danger fw-bold mt-2 text-uppercase">
                            <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                        </div>
                    @enderror

                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <label class="neo-form-label mb-0 fs-6 text-uppercase">Data Menu <span
                                    class="text-muted fw-normal text-lowercase">(Opsional)</span></label>
                            <button type="button" class="btn btn-outline-dark neo-btn py-1 px-3"
                                style="font-size: 0.85rem;" id="addMenuSubmissionItem">
                                <i class="fas fa-plus me-1"></i>Tambah
                            </button>
                        </div>

                        <div id="menuSubmissionList" class="d-grid gap-3">
                            @for ($i = 0; $i < $maxMenuRows; $i++)
                                <div class="neo-box p-3 bg-white" data-menu-item>
                                    <div class="row g-3 align-items-end">
                                        <div class="col-md-4">
                                            <label class="neo-form-label" style="font-size: 0.75rem;">Nama
                                                Menu</label>
                                            <input type="text" name="menu_nama[]" class="form-control neo-input"
                                                value="{{ $oldMenuNames[$i] ?? '' }}" placeholder="Ayam Bakar">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="neo-form-label" style="font-size: 0.75rem;">Harga
                                                (Rp)</label>
                                            <input type="number" step="0.01" min="0" name="menu_harga[]"
                                                class="form-control neo-input" value="{{ $oldMenuPrices[$i] ?? '' }}"
                                                placeholder="25000">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="neo-form-label" style="font-size: 0.75rem;">Foto
                                                Menu</label>
                                            <input type="file" name="menu_foto[]" class="form-control neo-input"
                                                style="padding-top: 0.2rem;" accept="image/*">
                                        </div>
                                        <div class="col-md-1 d-grid">
                                            <button type="button" class="btn neo-btn-danger"
                                                style="padding: 0.45rem;" data-remove-menu-item title="Hapus menu">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>

                    <div class="col-12 mt-4">
                        <label class="neo-form-label text-uppercase">Upload Buku/Daftar Menu Lengkap <span
                                class="text-muted fw-normal text-lowercase">(Opsional)</span></label>
                        <input type="file" name="menu_daftar_foto[]" class="form-control neo-input"
                            style="padding-top: 0.35rem;" accept="image/*" multiple>
                        <small class="text-muted fw-bold mt-1 d-block">Unggah satu atau lebih foto daftar menu jika
                            tidak ingin mengisi form satu per satu.</small>
                    </div>
                </div>
            </div>

            <!-- FOOTER DENGAN TOMBOL NEO -->
            <div class="modal-footer neo-modal-footer border-top-0 d-flex justify-content-between">
                <button type="button" class="neo-btn-outline m-0" data-bs-dismiss="modal">BATAL</button>
                <button type="submit" class="neo-btn-solid m-0">
                    <i class="fas fa-paper-plane me-2"></i>KIRIM PENGAJUAN
                </button>
            </div>

        </form>
    </div>
</div>

<!-- NEO FLASH MESSAGES GLOBAL -->
<div class="fixed-top px-3 pt-3" style="z-index: 1116; pointer-events: none;">
    <div class="container d-flex flex-column align-items-end gap-2" id="neo-flash-container"
        style="pointer-events: auto;">

        <!-- HANYA MUNCUL JIKA SERVER MENGIRIM STATUS SUKSES -->
        @if (session('success'))
            <div class="neo-alert-flash neo-alert-success fade show d-flex align-items-center justify-content-between p-3"
                role="alert">
                <div class="fw-black text-uppercase me-4" style="color: #000;">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                </div>
                <button type="button" class="neo-btn-square-close" onclick="this.parentElement.remove()"
                    style="background: transparent; border: 2px solid #000; padding: 2px 8px; font-weight: 900; cursor: pointer;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        @endif

        <!-- HANYA MUNCUL JIKA SERVER MENGIRIM STATUS GAGAL SISTEM/DATABASE -->
        @if (session('error'))
            <div class="neo-alert-flash neo-alert-danger fade show d-flex align-items-center justify-content-between p-3"
                role="alert">
                <div class="fw-black text-uppercase me-4" style="color: #000;">
                    <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                </div>
                <button type="button" class="neo-btn-square-close" onclick="this.parentElement.remove()"
                    style="background: transparent; border: 2px solid #000; padding: 2px 8px; font-weight: 900; cursor: pointer;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        @endif
        {{--
        @error('menu_kosong')
            <div id="alert-timer" class="neo-alert-flash neo-alert-danger fade show d-flex align-items-center justify-content-between p-3" role="alert">
                <div class="fw-black text-uppercase me-4">
                    <i class="fas fa-times-circle me-2"></i>{{ $message }}
                </div>
                <button type="button" class="neo-btn-square-close" onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        @enderror --}}

    </div>
</div>

<!-- Script Auto-Remove Flash Message (5 Detik) -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const flashMessages = document.querySelectorAll('.neo-alert-flash');
        flashMessages.forEach(function(message) {
            setTimeout(function() {
                message.style.transition = "opacity 0.5s ease";
                message.style.opacity = "0";
                setTimeout(() => message.remove(), 500);
            }, 5000);
        });
    });
</script>

<!-- Neo-Brutalism Loading Overlay -->
<div id="neoFormLoader" class="neo-loader-overlay d-none">
    <div class="neo-loader-box">
        <i class="fas fa-spinner fa-spin neo-loader-icon"></i>
        <h4 class="fw-black text-uppercase mt-3 mb-1" style="-webkit-text-stroke: 0.5px #000;">Memproses...</h4>
        <p class="fw-bold small mb-0">Mohon tunggu, data sedang dikirim.</p>
    </div>
</div>
{{-- menu submission modal behavior moved to resources/js/refactor/map-modals.js --}}

<div class="modal fade" id="imageLightboxModal" data-bs-backdrop="false" tabindex="-1" aria-labelledby="imageLightboxLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 bg-transparent shadow-none lightbox-modal-content">
            <div class="modal-header border-0 pb-1 lightbox-modal-header">
                <h6 class="modal-title" id="imageLightboxLabel">Preview Gambar</h6>
                <button type="button" class="btn-close btn-close-black" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body text-center pt-0 lightbox-modal-body">
                <div class="lightbox-stage">
                    <div class="lightbox-zoom-controls" aria-label="Kontrol zoom foto">
                        <button type="button" class="btn btn-light btn-sm lightbox-zoom-btn" id="lightboxZoomOutBtn"
                            title="Zoom out">
                            <i class="fas fa-minus"></i>
                        </button>
                        <button type="button" class="btn btn-light btn-sm lightbox-zoom-btn"
                            id="lightboxResetZoomBtn" title="Reset zoom">Reset</button>
                        <button type="button" class="btn btn-light btn-sm lightbox-zoom-btn" id="lightboxZoomInBtn"
                            title="Zoom in">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    <div class="lightbox-preview-box">
                        <img id="imageLightboxPreview" src="" alt="Preview" class="lightbox-preview-img">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if (
    $errors->any() &&
        old('id_umkm') &&
        (old('menu_nama') || $errors->has('menu_daftar_foto') || $errors->has('menu_daftar_foto.*')))
@endif

<a href="{{ url('/') }}" class="btn-back-landing">
    <i class="fas fa-arrow-left"></i>
    <span>Kembali</span>
</a>
@include('partials.umkm-submission-modal')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css">
    @vite('resources/css/map.css')
@endpush

@push('scripts')

    <!-- Script Buka Otomatis Modal Rating -->
    @if ($errors->hasAny(['nama_pengulas', 'nilai_rating', 'komentar']))
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                var ratingModalEl = document.getElementById('ratingModal');
                if (ratingModalEl) {
                    var ratingModal = new bootstrap.Modal(ratingModalEl, { backdrop: false });
                    ratingModal.show();
                }
            });
        </script>
    @endif

    <!-- Script Buka Otomatis Modal Pengajuan Menu -->
    @if ($errors->any() && old('tipe_form') === 'form_menu')
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                var menuModalEl = document.getElementById('menuSubmissionModal');
                if (menuModalEl) {
                    var menuModal = new bootstrap.Modal(menuModalEl, { backdrop: false });
                    menuModal.show();
                }
            });
        </script>
    @endif

    <script id="mapPageConfig" type="application/json">
        {!! json_encode([
        'landingUrl' => route('landing'),
        'mapDataUrl' => route('data-umkm.map-data'),
        'ratingStoreUrl' => route('rating.store'),
        'umkmDetailUrlTemplate' => route('umkm.detail', ['umkm' => '__UMKM__']),
        'umkmTrackUrlTemplate' => route('umkm.track', ['umkm' => '__UMKM__']),
        'selectedUmkm' => $selectedUmkm && $selectedUmkm->lokasi ? [
            'id' => $selectedUmkm->id_umkm,
            'latitude' => (float) $selectedUmkm->lokasi->latitude,
            'longitude' => (float) $selectedUmkm->lokasi->longitude,
        ] : null,
        'upiCenter' => [
            'latitude' => -6.861082410263256,
            'longitude' => 107.59205888361987,
            'radius' => 1000,
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>

    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
    @vite(['resources/js/map.js', 'resources/js/refactor/map-modals.js'])
@endpush
