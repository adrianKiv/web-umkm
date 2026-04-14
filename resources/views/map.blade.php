@extends('layouts.maplay')

@section('title', 'Peta UMKM' . ($selectedUmkm ? ' - ' . $selectedUmkm->nama_umkm : ''))

@section('contentmap')

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show"
            style="position: absolute; top: 1rem; right: 1rem; z-index: 1200;">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show"
            style="position: absolute; top: 1rem; right: 1rem; z-index: 1200;">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($dataUmkms->isEmpty())
        <div class="alert alert-warning" style="position: absolute; top: 1rem; left: 1rem; z-index: 999;">Tidak ada data
            UMKM.
        </div>
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
            <div class="detail-header">
                <h4 class="mb-0">{{ $selectedUmkm->nama_umkm }}</h4>
                <button type="button" class="custom-btn-close" onclick="closeDetailPanel()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="detail-content">
                <div class="detail-section">
                    <img src="{{ $selectedUmkm->foto_umkm_url }}" alt="Foto {{ $selectedUmkm->nama_umkm }}" class="detail-umkm-photo"
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
                    @if ($selectedUmkm->menu->isNotEmpty())
                        <div class="menu-list d-grid gap-2">
                            @foreach ($selectedUmkm->menu as $menu)
                                <div class="menu-item d-flex align-items-center gap-2">
                                    <img src="{{ $menu->foto_menu_url }}" alt="Foto {{ $menu->nama_menu }}" class="menu-thumb"
                                        onerror="this.onerror=null;this.src='{{ asset('images/default-menu.svg') }}';">
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold">{{ $menu->nama_menu }}</div>
                                        <small class="text-muted">Rp{{ number_format((float) $menu->harga_menu, 0, ',', '.') }}</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="mb-0 text-muted">Belum ada data menu.</p>
                    @endif
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
                    <div class="live-track-status text-muted small mt-2 w-100">Live tracking belum dimulai.</div>
                </div>
            </div>
        </div>
    @endif

    <div id="globalStopTrackingWrapper" class="global-stop-tracking d-none">
        <button type="button" id="globalStopTrackingBtn" class="btn btn-danger btn-sm shadow">
            <i class="fas fa-stop-circle me-1"></i>Stop Live Tracking
        </button>
    </div>
@endsection

<!-- Rating Modal -->
<div class="modal fade" id="ratingModal" tabindex="-1" aria-labelledby="ratingModalLabel" aria-hidden="true">
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
<a href="{{ url('/') }}" class="btn-back-landing">
    <i class="fas fa-arrow-left"></i>
    <span>Kembali</span>
</a>

@include('partials.umkm-submission-modal')

@push('styles')
    @vite('resources/css/map.css')
@endpush

@push('scripts')
    @php
        $mapUmkms = $dataUmkms
            ->filter(fn($item) => optional($item->lokasi)->latitude && optional($item->lokasi)->longitude)
            ->map(function ($item) {
                return [
                    'id' => $item->id_umkm,
                    'nama_umkm' => $item->nama_umkm,
                    'foto_umkm_url' => $item->foto_umkm_url,
                    'kategori' => optional($item->kategori)->nama_kategori ?? 'Tidak dikategorikan',
                    'kelompok' => optional(optional($item->kategori)->kelompok)->nama_kelompok ?? 'Tanpa Kelompok',
                    'jam_buka' => $item->jam_buka,
                    'alamat_lengkap' => $item->alamat_lengkap,
                    'deskripsi' => $item->deskripsi,
                    'latitude' => (float) $item->lokasi->latitude,
                    'longitude' => (float) $item->lokasi->longitude,
                    'rating_avg' => (float) ($item->rating->avg('nilai_rating') ?? 0),
                    'rating_count' => (int) $item->rating->count(),
                    'ulasan' => $item->rating
                        ->sortByDesc('created_at')
                        ->map(
                            fn($rating) => [
                                'nama_pengulas' => $rating->nama_pengulas ?: 'Anonymous',
                                'nilai_rating' => (int) $rating->nilai_rating,
                                'komentar' => $rating->komentar,
                                'tanggal' => optional($rating->created_at)->format('Y-m-d H:i:s'),
                            ],
                        )
                        ->values(),
                    'menu' => $item->menu
                        ->map(
                            fn($menu) => [
                                'id' => $menu->id_menu,
                                'nama_menu' => $menu->nama_menu,
                                'harga_menu' => (float) $menu->harga_menu,
                                'foto_menu_url' => $menu->foto_menu_url,
                            ],
                        )
                        ->values(),
                ];
            })
            ->values();

        $selectedPayload = null;
        if ($selectedUmkm && $selectedUmkm->lokasi) {
            $selectedPayload = [
                'id' => $selectedUmkm->id_umkm,
                'latitude' => (float) $selectedUmkm->lokasi->latitude,
                'longitude' => (float) $selectedUmkm->lokasi->longitude,
            ];
        }
    @endphp

    <script id="mapPageConfig" type="application/json">
        {!! json_encode([
        'landingUrl' => route('landing'),
        'ratingStoreUrl' => route('rating.store'),
        'umkms' => $mapUmkms,
        'selectedUmkm' => $selectedPayload,
        'upiCenter' => [
            'latitude' => -6.861082410263256,
            'longitude' => 107.59205888361987,
            'radius' => 1000,
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>

    @vite('resources/js/map.js')
@endpush
