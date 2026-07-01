@extends('layouts.maplay')

@section('title', 'Peta UMKM' . ($selectedUmkm ? ' - ' . $selectedUmkm->nama_umkm : ''))

@section('contentmap')

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show abs-top-right" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show abs-top-right" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($dataUmkms->isEmpty())
        <div class="alert alert-warning abs-top-left">Tidak ada data UMKM.</div>
    @endif

    <div class="map-controls" id="mapControls">
        <div class="map-search-row">
            <input type="text" id="mapSearchInput" class="form-control form-control-sm"
                placeholder="Cari nama UMKM atau alamat...">
            <button type="button" class="btn btn-primary btn-sm" id="mapSearchBtn" title="Cari UMKM">
                <i class="fas fa-search"></i>
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="toggleSearchFiltersBtn"
                title="Tampilkan filter pencarian">
                <i class="fas fa-filter"></i>
            </button>
        </div>

        <div id="searchFilterDropdown" class="search-filter-dropdown d-none mt-2">
            <div class="map-category-chips" id="categoryChips"></div>

            <div class="d-flex align-items-center justify-content-between mt-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="toggleMoreFiltersBtn">
                    <i class="fas fa-sliders-h me-1"></i>Filter Lainnya
                </button>
                <small id="mapResultInfo" class="text-muted">Menampilkan semua UMKM</small>
            </div>
        </div>

        <button type="button" class="btn btn-primary btn-sm w-100 mt-2" data-bs-toggle="modal"
            onclick="if (typeof closeDetailPanel === 'function') closeDetailPanel();"
            data-bs-target="#umkmSubmissionModal">
            <i class="fas fa-plus-circle me-1"></i>Daftarkan UMKM
        </button>

        <div id="desktopFilterPanel" class="desktop-filter-panel d-none mt-2">
            <label class="form-label mb-1">Kelompok UMKM</label>
            <select id="desktopGroupFilter" class="form-select form-select-sm mb-2">
                <option value="all">Semua Kelompok</option>
            </select>

            <label class="form-label mb-1">Rating Minimal</label>
            <select id="desktopMinRating" class="form-select form-select-sm mb-2">
                <option value="0">Semua Rating</option>
                <option value="1">1.0 ke atas</option>
                <option value="2">2.0 ke atas</option>
                <option value="3">3.0 ke atas</option>
                <option value="4">4.0 ke atas</option>
                <option value="4.5">4.5 ke atas</option>
                <option value="5">5.0 ke atas</option>
            </select>

            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="desktopOpenNow">
                <label class="form-check-label" for="desktopOpenNow">Sedang Buka</label>
            </div>

            <div class="d-flex gap-2">
                <button type="button" class="btn btn-primary btn-sm w-100" id="desktopApplyFilters">Terapkan</button>
                <button type="button" class="btn btn-outline-secondary btn-sm w-100"
                    id="desktopResetFilters">Reset</button>
            </div>
        </div>
    </div>

    <div id="mobileFilterBackdrop" class="mobile-filter-backdrop d-none"></div>
    <div id="mobileFilterSheet" class="mobile-filter-sheet d-none">
        <div class="sheet-handle"></div>
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="mb-0">Filter Lainnya</h6>
            <button type="button" class="btn btn-sm btn-light" id="closeMobileFilterSheet">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <label class="form-label mb-1">Kelompok UMKM</label>
        <select id="mobileGroupFilter" class="form-select form-select-sm mb-3">
            <option value="all">Semua Kelompok</option>
        </select>

        <label class="form-label mb-1">Rating Minimal</label>
        <select id="mobileMinRating" class="form-select form-select-sm mb-3">
            <option value="0">Semua Rating</option>
            <option value="1">1.0 ke atas</option>
            <option value="2">2.0 ke atas</option>
            <option value="3">3.0 ke atas</option>
            <option value="4">4.0 ke atas</option>
            <option value="4.5">4.5 ke atas</option>
            <option value="5">5.0 ke atas</option>
        </select>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="mobileOpenNow">
            <label class="form-check-label" for="mobileOpenNow">Sedang Buka</label>
        </div>

        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary btn-sm w-100" id="mobileApplyFilters">Terapkan</button>
            <button type="button" class="btn btn-outline-secondary btn-sm w-100" id="mobileResetFilters">Reset</button>
        </div>
    </div>
    <!-- UMKM Detail Panel -->
    @if ($selectedUmkm)
        <div id="umkm-detail-panel" class="umkm-detail-panel">
            {{-- <div class="detail-sheet-handle"></div> --}}
            <div class="detail-header">
                <h4 class="mb-0">{{ $selectedUmkm->nama_umkm }}</h4>
                <button type="button" class="custom-btn-close" onclick="closeDetailPanel()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="detail-content">
                <div class="detail-section">
                    <img src="{{ $selectedUmkm->foto_umkm_url }}" alt="Foto {{ $selectedUmkm->nama_umkm }}" class="detail-umkm-photo lightbox-trigger"
                        onclick="openImageLightbox('{{ $selectedUmkm->foto_umkm_url }}', 'Foto {{ addslashes($selectedUmkm->nama_umkm) }}')"
                        onerror="this.onerror=null;this.src='{{ asset('images/default-umkm.svg') }}';">
                </div>

                <div class="detail-section">
                    <h6><i class="fas fa-tag me-2"></i>Kategori</h6>
                    <span
                        class="badge bg-primary">{{ optional($selectedUmkm->kategori)->nama_kategori ?? 'Tidak dikategorikan' }}</span>
                </div>

                <div class="detail-section">
                    <h6><i class="fas fa-clock me-2"></i>Jam Buka</h6>
                    <p class="mb-0">{{ $selectedUmkm->jam_buka }}</p>
                </div>

                <div class="detail-section">
                    <h6><i class="fas fa-map-marker-alt me-2"></i>Alamat Lengkap</h6>
                    <p class="mb-0">{{ $selectedUmkm->alamat_lengkap }}</p>
                </div>

                <div class="detail-section">
                    <h6><i class="fas fa-phone me-2"></i>No telfon</h6>
                    <p class="mb-0">{{ $selectedUmkm->no_telfon }}</p>
                </div>

                <div class="detail-section">
                    <h6><i class="fas fa-star me-2"></i>Rating</h6>
                    @php
                        $avgRating = $selectedUmkm->rating->avg('nilai_rating') ?? 0;
                        $ratingCount = $selectedUmkm->rating->count();
                    @endphp
                    <div class="d-flex align-items-center">
                        <div class="stars me-2">
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
                        <small class="text-muted">({{ number_format($avgRating, 1) }} • {{ $ratingCount }} ulasan)</small>
                    </div>
                    <button type="button" class="btn btn-link btn-sm p-0 mt-2"
                        onclick="toggleUlasan('ulasan-selected', this)">
                        <i class="fas fa-comments me-1"></i>Lihat ulasan
                    </button>
                    <div id="ulasan-selected" class="ulasan-list-container d-none mt-2">
                        @forelse ($selectedUmkm->rating->sortByDesc('created_at') as $ulasan)
                            <div class="ulasan-item">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <strong>{{ $ulasan->nama_pengulas ?: 'Anonymous' }}</strong>
                                    <small
                                        class="text-muted">{{ optional($ulasan->created_at)->format('d M Y') ?? '-' }}</small>
                                </div>
                                <div class="stars mb-1">
                                    @for ($i = 1; $i <= 5; $i++)
                                        @if ($i <= $ulasan->nilai_rating)
                                            <i class="fas fa-star text-warning"></i>
                                        @else
                                            <i class="far fa-star text-warning"></i>
                                        @endif
                                    @endfor
                                </div>
                                <p class="mb-0">{{ $ulasan->komentar ?: 'Pengguna tidak menulis ulasan.' }}</p>
                            </div>
                        @empty
                            <p class="mb-0 text-muted">Belum ada ulasan untuk UMKM ini.</p>
                        @endforelse
                    </div>
                </div>

                @if ($selectedUmkm->deskripsi)
                    <div class="detail-section">
                        <h6><i class="fas fa-info-circle me-2"></i>Deskripsi</h6>
                        <p class="mb-0">{{ $selectedUmkm->deskripsi }}</p>
                    </div>
                @endif

                <div class="detail-section">
                    <h6><i class="fas fa-utensils me-2"></i>Menu UMKM</h6>
                    @php
                        $menuItems = $selectedUmkm->menu->filter(fn($menu) => !$menu->is_foto_daftar_menu);
                        $menuGallery = $selectedUmkm->menu->filter(fn($menu) => $menu->is_foto_daftar_menu && $menu->foto_menu && $menu->foto_menu !== '-');
                    @endphp

                    @if ($menuItems->isNotEmpty())
                        <div class="menu-list d-grid gap-2">
                            @foreach ($menuItems as $menu)
                                <div class="menu-item d-flex align-items-center gap-2">
                                    <img src="{{ $menu->foto_menu_url }}" alt="Foto {{ $menu->nama_menu }}" class="menu-thumb lightbox-trigger"
                                        onclick="openImageLightbox('{{ $menu->foto_menu_url }}', 'Foto {{ addslashes($menu->nama_menu) }}')"
                                        onerror="this.onerror=null;this.src='{{ asset('images/default-menu.svg') }}';">
                                    <div class="grow">
                                        <div class="fw-semibold">{{ $menu->nama_menu }}</div>
                                        <small class="text-muted">
                                            {{ (float) $menu->harga_menu > 0 ? 'Rp ' . number_format((float) $menu->harga_menu, 0, ',', '.') : '- (harga belum dimasukan)' }}
                                        </small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="mb-1 text-muted">Belum ada data menu dengan nama dan harga.</p>
                    @endif

                    @if ($menuGallery->isNotEmpty())
                        <div class="mt-2">
                            <small class="text-muted fw-semibold d-block text-bold mb-2">Foto daftar menu</small>
                            <div class="menu-gallery d-flex flex-wrap gap-2">
                                @foreach ($menuGallery as $menuPhoto)
                                    <img src="{{ $menuPhoto->foto_menu_url }}" alt="Foto daftar menu {{ $selectedUmkm->nama_umkm }}"
                                        class="menu-gallery-thumb lightbox-trigger"
                                        onclick="openImageLightbox('{{ $menuPhoto->foto_menu_url }}', 'Foto daftar menu {{ addslashes($selectedUmkm->nama_umkm) }}')"
                                        onerror="this.onerror=null;this.src='{{ asset('images/default-menu.svg') }}';">
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <button type="button" class="btn btn-outline-primary btn-sm mt-2"
                        onclick="openMenuSubmissionModal({{ $selectedUmkm->id_umkm }}, '{{ addslashes($selectedUmkm->nama_umkm) }}')">
                        <i class="fas fa-plus-circle me-1"></i>Ajukan Menu Baru
                    </button>
                </div>

                <div class="detail-actions mt-3">
                    <div class="row g-2">
                        <div class="col-6">
                            <button type="button" class="btn btn-success btn-sm w-100"
                                onclick="openRatingModal({{ $selectedUmkm->id_umkm }}, '{{ addslashes($selectedUmkm->nama_umkm) }}')">
                                <i class="fas fa-star me-1"></i>Beri Rating
                            </button>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('landing') }}" class="btn btn-outline-primary btn-sm w-100">
                                <i class="fas fa-arrow-left me-1"></i>Kembali
                            </a>
                        </div>
                        @if ($selectedUmkm->lokasi)
                            <div class="col-6">
                                <button type="button" class="btn btn-info btn-sm w-100"
                                    onclick="startLiveTrackingTo({{ (float) $selectedUmkm->lokasi->latitude }}, {{ (float) $selectedUmkm->lokasi->longitude }}, '{{ addslashes($selectedUmkm->nama_umkm) }}')">
                                    <i class="fas fa-route me-1"></i>Live Track
                                </button>
                            </div>
                        @endif
                    </div>
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
<div class="modal fade" id="ratingModal" tabindex="-1" aria-labelledby="ratingModalLabel">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ratingModalLabel">
                    <i class="fas fa-star text-warning me-2"></i>Beri Rating untuk <span id="umkmName"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="ratingForm">
                <div class="modal-body">
                    <input type="hidden" id="ratingUmkmId" name="id_umkm">

                    <div class="mb-3">
                        <label for="namaPengulas" class="form-label">
                            <i class="fas fa-user me-1"></i>Nama Anda <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="namaPengulas" name="nama_pengulas" required
                            placeholder="Masukkan nama Anda">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-star me-1"></i>Rating <span class="text-danger">*</span>
                        </label>
                        <div class="rating-stars">
                            <input type="hidden" id="nilaiRating" name="nilai_rating" value="0">
                            <div class="stars-container">
                                <i class="far fa-star star" data-rating="1"></i>
                                <i class="far fa-star star" data-rating="2"></i>
                                <i class="far fa-star star" data-rating="3"></i>
                                <i class="far fa-star star" data-rating="4"></i>
                                <i class="far fa-star star" data-rating="5"></i>
                            </div>
                            <small class="text-muted mt-1 d-block" id="ratingText">Belum dipilih</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="komentar" class="form-label">
                            <i class="fas fa-comment me-1"></i>Komentar (Opsional)
                        </label>
                        <textarea class="form-control" id="komentar" name="komentar" rows="3"
                            placeholder="Berikan komentar atau ulasan Anda..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-paper-plane me-1"></i>Kirim Rating
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- rating modal behavior moved to resources/js/refactor/map-modals.js --}}

<div class="modal fade" id="menuSubmissionModal" data-show-on-errors="{{ ($errors->any() && old('id_umkm') && (old('menu_nama') || $errors->has('menu_daftar_foto') || $errors->has('menu_daftar_foto.*'))) ? '1' : '0' }}" tabindex="-1" aria-labelledby="menuSubmissionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="menuSubmissionModalLabel">
                    <i class="fas fa-utensils text-primary me-2"></i>Ajukan Menu Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('menu-submissions.store') }}" enctype="multipart/form-data">
                @csrf
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
                <div class="modal-body">
                    <input type="hidden" id="menuSubmissionUmkmId" name="id_umkm" value="{{ old('id_umkm') }}">

                    <div class="mb-3">
                        <label class="form-label">UMKM Tujuan</label>
                        <div id="menuSubmissionTargetName" class="form-control bg-light">{{ $oldMenuTargetName ?: '-' }}</div>
                    </div>

                    <div class="mb-3">
                        <label for="menuSubmissionPengusul" class="form-label">Nama Pengusul <span class="text-danger">*</span></label>
                        <input type="text" id="menuSubmissionPengusul" name="nama_pengusul" class="form-control"
                            value="{{ old('nama_pengusul') }}" required>
                        @error('nama_pengusul')<span class="text-danger small d-block mt-1">{{ $message }}</span>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="menuSubmissionEmail" class="form-label">Email Pengusul</label>
                        <input type="email" id="menuSubmissionEmail" name="email_pengusul" class="form-control"
                            value="{{ old('email_pengusul') }}" placeholder="opsional">
                        @error('email_pengusul')<span class="text-danger small d-block mt-1">{{ $message }}</span>@enderror
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0">Data Menu</label>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="addMenuSubmissionItem">
                                <i class="fas fa-plus me-1"></i>Tambah Menu
                            </button>
                        </div>

                        <div id="menuSubmissionList" class="d-grid gap-2">
                            @for ($i = 0; $i < $maxMenuRows; $i++)
                                <div class="border rounded-3 p-2 submission-menu-item" data-menu-item>
                                    <div class="row g-2 align-items-end">
                                        <div class="col-md-5">
                                            <label class="form-label small">Nama Menu</label>
                                            <input type="text" name="menu_nama[]" class="form-control form-control-sm"
                                                value="{{ $oldMenuNames[$i] ?? '' }}" placeholder="Contoh: Ayam Bakar">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small">Harga</label>
                                            <input type="number" step="0.01" min="0" name="menu_harga[]"
                                                class="form-control form-control-sm" value="{{ $oldMenuPrices[$i] ?? '' }}"
                                                placeholder="Contoh: 25000">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small">Foto Menu</label>
                                            <input type="file" name="menu_foto[]" class="form-control form-control-sm" accept="image/*">
                                        </div>
                                        <div class="col-md-1 d-grid">
                                            <button type="button" class="btn btn-sm btn-outline-danger" data-remove-menu-item title="Hapus menu">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endfor
                        </div>

                        @if ($errors->has('menu_nama') || $errors->has('menu_nama.*') || $errors->has('menu_harga') || $errors->has('menu_harga.*') || $errors->has('menu_foto') || $errors->has('menu_foto.*'))
                            <div class="text-danger small mt-2">
                                @foreach (array_merge($errors->get('menu_nama'), $errors->get('menu_nama.*'), $errors->get('menu_harga'), $errors->get('menu_harga.*'), $errors->get('menu_foto'), $errors->get('menu_foto.*')) as $messages)
                                    @foreach ((array) $messages as $message)
                                        <div>{{ $message }}</div>
                                    @endforeach
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Foto Daftar Menu (Opsional)</label>
                        <input type="file" name="menu_daftar_foto[]" class="form-control" accept="image/*" multiple>
                        <small class="text-muted">Unggah satu atau lebih foto daftar menu, tanpa wajib isi data menu (nama/harga menu).</small>
                        @if ($errors->has('menu_daftar_foto') || $errors->has('menu_daftar_foto.*'))
                            <div class="text-danger small mt-2">
                                @foreach (array_merge($errors->get('menu_daftar_foto'), $errors->get('menu_daftar_foto.*')) as $messages)
                                    @foreach ((array) $messages as $message)
                                        <div>{{ $message }}</div>
                                    @endforeach
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-1"></i>Kirim Pengajuan Menu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- menu submission modal behavior moved to resources/js/refactor/map-modals.js --}}

<div class="modal fade" id="imageLightboxModal" tabindex="-1" aria-labelledby="imageLightboxLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 bg-transparent shadow-none lightbox-modal-content">
            <div class="modal-header border-0 pb-1 lightbox-modal-header">
                <h6 class="modal-title text-white" id="imageLightboxLabel">Preview Gambar</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center pt-0 lightbox-modal-body">
                <div class="lightbox-stage">
                    <div class="lightbox-zoom-controls" aria-label="Kontrol zoom foto">
                        <button type="button" class="btn btn-light btn-sm lightbox-zoom-btn" id="lightboxZoomOutBtn" title="Zoom out">
                            <i class="fas fa-minus"></i>
                        </button>
                        <button type="button" class="btn btn-light btn-sm lightbox-zoom-btn" id="lightboxResetZoomBtn" title="Reset zoom">Reset</button>
                        <button type="button" class="btn btn-light btn-sm lightbox-zoom-btn" id="lightboxZoomInBtn" title="Zoom in">
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

@if ($errors->any() && old('id_umkm') && (old('menu_nama') || $errors->has('menu_daftar_foto') || $errors->has('menu_daftar_foto.*')))
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
    @vite(['resources/js/map.js','resources/js/refactor/map-modals.js'])
@endpush
