@extends('admin.layout')

@section('title', 'ADMIN DASHBOARD - UMKM Kuliner')

@section('admin-content')
    <div class="dashboard-shell">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <div class="section-label">Overview</div>
                <h2 class="page-title mb-1">Admin Dashboard</h2>
                <div class="page-subtitle">Pantau pengajuan, performa, dan kualitas data UMKM.</div>
            </div>
            <div class="text-muted small">Update otomatis dari sistem.</div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-6 col-xl-3">
                <div class="stat-tile tile-sunrise">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label">Total UMKM</div>
                            <div class="stat-value">{{ $stats['total_umkm'] }}</div>
                            <div class="stat-meta">Unit terdata saat ini</div>
                        </div>
                        <span class="stat-icon"><i class="fas fa-store"></i></span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <div class="stat-tile tile-sky">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label">Total Rating</div>
                            <div class="stat-value">{{ $stats['total_rating'] }}</div>
                            <div class="stat-meta">Jumlah ulasan masuk</div>
                        </div>
                        <span class="stat-icon"><i class="fas fa-star"></i></span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <div class="stat-tile tile-emerald">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label">Rata-rata Rating</div>
                            <div class="stat-value">{{ number_format($stats['avg_rating'], 1) }}</div>
                            <div class="stat-meta">Skor kualitas UMKM</div>
                        </div>
                        <span class="stat-icon"><i class="fas fa-chart-bar"></i></span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <div class="stat-tile tile-coral">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label">Total Menu</div>
                            <div class="stat-value">{{ $stats['total_menu'] }}</div>
                            <div class="stat-meta">Menu terverifikasi</div>
                        </div>
                        <span class="stat-icon"><i class="fas fa-utensils"></i></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 mb-4">
                <div class="card card-modern">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <span class="icon-circle icon-primary"><i class="fas fa-user-check"></i></span>
                            <h5 class="mb-0 header-title">Konfirmasi Pengajuan Data UMKM</h5>
                        </div>
                        <span class="badge badge-soft-primary rounded-pill">{{ $pendingSubmissions->count() }}
                            pending</span>
                    </div>
                    <div class="card-body p-0">
                        @if ($pendingSubmissions->isEmpty())
                            <div class="p-4 text-center text-muted empty-state">
                                Tidak ada pengajuan yang menunggu konfirmasi.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 align-middle table-modern">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Pengusul</th>
                                            <th>Nama UMKM</th>
                                            <th>Foto</th>
                                            <th>Kategori</th>
                                            <th>Kontak</th>
                                            <th>Menu Diajukan</th>
                                            <th>Koordinat</th>
                                            <th>Waktu</th>
                                            <th class="text-end">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($pendingSubmissions as $submission)
                                            <tr>
                                                <td>
                                                    <div class="fw-semibold">{{ $submission->nama_pengusul }}</div>
                                                    <small
                                                        class="text-muted">{{ $submission->email_pengusul ?: '-' }}</small>
                                                </td>
                                                <td>
                                                    <div class="fw-semibold" title="{{ $submission->nama_umkm }}">
                                                        {{ \Illuminate\Support\Str::limit($submission->nama_umkm, 32) }}
                                                    </div>
                                                    <small
                                                        class="text-muted">{{ \Illuminate\Support\Str::limit($submission->alamat_lengkap, 45) }}</small>
                                                </td>
                                                <td>
                                                    <img src="{{ $submission->foto_umkm_url }}"
                                                        alt="Foto {{ $submission->nama_umkm }}"
                                                        class="thumb-56 lightbox-trigger" style="cursor: zoom-in;"
                                                        onclick="openImagePreview(this.src, this.alt)"
                                                        onerror="this.onerror=null;this.src='{{ asset('images/default-umkm.svg') }}';">
                                                </td>
                                                <td>{{ $submission->kategori->nama_kategori ?? '-' }}</td>
                                                <td>{{ $submission->no_telfon }}</td>
                                                <td>
                                                    @php
                                                        $menuCount = $submission->menuSubmissions->count();
                                                    @endphp
                                                    @if ($menuCount > 0)
                                                        <button type="button" class="btn btn-sm btn-outline-info"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#submissionMenuDetailModal{{ $submission->id }}">
                                                            <i class="fas fa-utensils me-1"></i>{{ $menuCount }} Menu
                                                        </button>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <small>{{ $submission->latitude }},
                                                        {{ $submission->longitude }}</small>
                                                </td>
                                                <td><small>{{ $submission->created_at?->format('d M Y H:i') }}</small></td>
                                                <td class="text-end">
                                                    <div class="d-flex justify-content-end gap-2">
                                                        <form
                                                            action="{{ route('admin.submissions.approve', $submission) }}"
                                                            method="POST" class="d-inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="btn btn-sm btn-success"
                                                                onclick="return confirm('Setujui dan publikasikan UMKM ini?')">
                                                                <i class="fas fa-check me-1"></i>Terima
                                                            </button>
                                                        </form>
                                                        <form action="{{ route('admin.submissions.reject', $submission) }}"
                                                            method="POST" class="d-inline"
                                                            onsubmit="return confirm('Tolak pengajuan ini?')">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                                <i class="fas fa-times me-1"></i>Tolak
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            @foreach ($pendingSubmissions as $submission)
                                @if ($submission->menuSubmissions->count() > 0)
                                    <div class="modal fade" id="submissionMenuDetailModal{{ $submission->id }}"
                                        tabindex="-1"
                                        aria-labelledby="submissionMenuDetailModalLabel{{ $submission->id }}"
                                        aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title"
                                                        id="submissionMenuDetailModalLabel{{ $submission->id }}">
                                                        Menu Yang Diajukan Untuk
                                                        <span title="{{ $submission->nama_umkm }}">
                                                            {{ \Illuminate\Support\Str::limit($submission->nama_umkm, 36) }}
                                                        </span>
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row g-2">
                                                        @foreach ($submission->menuSubmissions as $menuSubmission)
                                                            <div class="col-md-6">
                                                                <div
                                                                    class="border rounded p-2 h-100 d-flex gap-2 align-items-start">
                                                                    <img src="{{ $menuSubmission->foto_menu_url }}"
                                                                        alt="Foto {{ $menuSubmission->nama_menu }}"
                                                                        class="thumb-56 lightbox-trigger"
                                                                        style="cursor: zoom-in;"
                                                                        onclick="openImagePreview(this.src, this.alt)"
                                                                        onerror="this.onerror=null;this.src='{{ asset('images/default-menu.svg') }}';">
                                                                    <div>
                                                                        @if ($menuSubmission->is_foto_daftar_menu)
                                                                            <div class="fw-semibold">Foto daftar menu</div>
                                                                            <small class="text-muted">Tanpa nama/harga
                                                                                menu</small>
                                                                        @else
                                                                            <div class="fw-semibold">
                                                                                {{ $menuSubmission->nama_menu }}</div>
                                                                            <small
                                                                                class="text-muted">Rp{{ number_format((float) $menuSubmission->harga_menu, 0, ',', '.') }}</small>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-12 mb-4">
                <div class="card card-modern">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <span class="icon-circle icon-info"><i class="fas fa-utensils"></i></span>
                            <h5 class="mb-0 header-title">Konfirmasi Pengajuan Data MENU</h5>
                        </div>
                        <span class="badge badge-soft-primary rounded-pill">{{ $pendingMenuSubmissions->count() }}
                            pending</span>
                    </div>
                    <div class="card-body p-0">
                        @if ($pendingMenuSubmissions->isEmpty())
                            <div class="p-4 text-center text-muted empty-state">
                                Tidak ada pengajuan menu yang menunggu konfirmasi.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 align-middle table-modern">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Menu</th>
                                            <th>Foto</th>
                                            <th>Pengusul</th>
                                            <th>UMKM Tujuan</th>
                                            <th>Tanggal</th>
                                            <th class="text-end">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($pendingMenuSubmissions as $menuSubmission)
                                            <tr>
                                                <td>
                                                    @if ($menuSubmission->is_foto_daftar_menu)
                                                        <div class="fw-semibold">Foto daftar menu</div>
                                                        <small class="text-muted">Tanpa nama/harga menu</small>
                                                    @else
                                                        <div class="fw-semibold">{{ $menuSubmission->nama_menu }}</div>
                                                        <small
                                                            class="text-muted">Rp{{ number_format((float) $menuSubmission->harga_menu, 0, ',', '.') }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <img src="{{ $menuSubmission->foto_menu_url }}"
                                                        alt="Foto {{ $menuSubmission->nama_menu }}"
                                                        class="thumb-56 lightbox-trigger" style="cursor: zoom-in;"
                                                        onclick="openImagePreview(this.src, this.alt)"
                                                        onerror="this.onerror=null;this.src='{{ asset('images/default-menu.svg') }}';">
                                                </td>
                                                <td>
                                                    <div class="fw-semibold">{{ $menuSubmission->nama_pengusul ?: '-' }}
                                                    </div>
                                                    <small
                                                        class="text-muted">{{ $menuSubmission->email_pengusul ?: '-' }}</small>
                                                </td>
                                                <td>
                                                    <span title="{{ $menuSubmission->umkm->nama_umkm ?? '-' }}">
                                                        {{ \Illuminate\Support\Str::limit($menuSubmission->umkm->nama_umkm ?? '-', 26) }}
                                                    </span>
                                                </td>
                                                <td><small>{{ $menuSubmission->created_at?->format('d M Y H:i') }}</small>
                                                </td>
                                                <td class="text-end">
                                                    <div class="d-flex justify-content-end gap-2">
                                                        <form
                                                            action="{{ route('admin.menu-submissions.approve', $menuSubmission) }}"
                                                            method="POST" class="d-inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="btn btn-sm btn-success"
                                                                onclick="return confirm('Setujui pengajuan menu ini?')">
                                                                <i class="fas fa-check me-1"></i>Setujui
                                                            </button>
                                                        </form>
                                                        <form
                                                            action="{{ route('admin.menu-submissions.reject', $menuSubmission) }}"
                                                            method="POST" class="d-inline"
                                                            onsubmit="return confirm('Tolak pengajuan menu ini?')">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                                <i class="fas fa-times me-1"></i>Tolak
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-lg-6 mb-4">
                <div class="card card-modern h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <span class="icon-circle icon-primary"><i class="fas fa-arrow-up"></i></span>
                            <h5 class="mb-0 header-title">Top 10 UMKM Terpopuler</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        @if ($topClicks->isEmpty())
                            <div class="text-muted text-center py-5 empty-state">Belum ada data klik UMKM.</div>
                        @else
                            <ol class="list-group list-group-numbered list-group-flush leaderboard">
                                @foreach ($topClicks as $umkm)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span class="text-truncate text-truncate-max-70" title="{{ $umkm->nama_umkm }}">
                                            {{ \Illuminate\Support\Str::limit($umkm->nama_umkm, 24) }}
                                        </span>
                                        <span
                                            class="badge badge-soft-primary rounded-pill">{{ (int) $umkm->total_klik }}</span>
                                    </li>
                                @endforeach
                            </ol>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4">
                <div class="card card-modern h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <span class="icon-circle icon-danger"><i class="fas fa-arrow-down"></i></span>
                            <h5 class="mb-0 header-title">Top 10 UMKM Kurang Populer</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        @if ($lowestClicks->isEmpty())
                            <div class="text-muted text-center py-5 empty-state">Belum ada data klik UMKM.</div>
                        @else
                            <ol class="list-group list-group-numbered list-group-flush leaderboard leaderboard-danger">
                                @foreach ($lowestClicks as $umkm)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span class="text-truncate text-truncate-max-70" title="{{ $umkm->nama_umkm }}">
                                            {{ \Illuminate\Support\Str::limit($umkm->nama_umkm, 24) }}
                                        </span>
                                        <span
                                            class="badge badge-soft-danger rounded-pill">{{ (int) $umkm->total_klik }}</span>
                                    </li>
                                @endforeach
                            </ol>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Data -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card card-modern h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <span class="icon-circle icon-success"><i class="fas fa-star"></i></span>
                            <h5 class="mb-0 header-title">Rata - Rata Rating per Kategori</h5>
                        </div>
                    </div>
                    <div class="card-body card-body-h-340">
                        @if ($ratingCategoryLabels->isEmpty())
                            <div class="text-muted text-center py-5 empty-state">Belum ada data rating kategori.</div>
                        @else
                            <div class="chart-wrap h-100">
                                <canvas id="ratingCategoryChart"></canvas>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card card-modern card-alert h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <span class="icon-circle icon-warning"><i class="fas fa-exclamation-triangle"></i></span>
                            <h5 class="mb-0 header-title">Pengecekan Kualitas Data</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-0">
                            Beberapa data UMKM di bawah ini belum lengkap dan mungkin tidak akan tampil optimal di Peta.
                        </p>

                        <ul class="list-group list-group-flush list-clean">
                            {{-- Item 1: Pengecekan Lokasi --}}
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <i class="fas fa-map-marker-alt text-danger me-2"></i>
                                    <span>UMKM tanpa koordinat lokasi</span>
                                </div>
                                @if ($umkmTanpaKoordinat > 0)
                                    <span class="badge bg-danger rounded-pill">{{ $umkmTanpaKoordinat }} UMKM</span>
                                @else
                                    <span class="badge bg-success rounded-pill"><i class="fas fa-check"></i> Aman</span>
                                @endif
                            </li>

                            {{-- Item 2: Pengecekan Alamat Lengkap --}}
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <i class="fas fa-map-signs text-warning me-2"></i>
                                    <span>UMKM tanpa alamat lengkap</span>
                                </div>
                                @if ($umkmTanpaAlamat > 0)
                                    <span class="badge bg-warning text-dark rounded-pill">{{ $umkmTanpaAlamat }}
                                        UMKM</span>
                                @else
                                    <span class="badge bg-success rounded-pill"><i class="fas fa-check"></i> Aman</span>
                                @endif
                            </li>

                            {{-- Item 3: Pengecekan Nomor Telepon --}}
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <i class="fas fa-phone-slash text-warning me-2"></i>
                                    <span>UMKM tanpa nomor telepon</span>
                                </div>
                                @if ($umkmTanpaTelepon > 0)
                                    <span class="badge bg-warning text-dark rounded-pill">{{ $umkmTanpaTelepon }}
                                        UMKM</span>
                                @else
                                    <span class="badge bg-success rounded-pill"><i class="fas fa-check"></i> Aman</span>
                                @endif
                            </li>

                            {{-- Item 4: Pengecekan Jam Buka --}}
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <i class="fas fa-clock text-info me-2"></i>
                                    <span>UMKM tanpa jam buka</span>
                                </div>
                                @if ($umkmTanpaJam > 0)
                                    <span class="badge bg-info text-dark rounded-pill">{{ $umkmTanpaJam }} UMKM</span>
                                @else
                                    <span class="badge bg-success rounded-pill"><i class="fas fa-check"></i> Aman</span>
                                @endif
                            </li>

                            {{-- Item 5: Pengecekan Foto UMKM --}}
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <i class="fas fa-image text-danger me-2"></i>
                                    <span>UMKM tanpa foto</span>
                                </div>
                                @if ($umkmTanpaFoto > 0)
                                    <span class="badge bg-danger rounded-pill">{{ $umkmTanpaFoto }} UMKM</span>
                                @else
                                    <span class="badge bg-success rounded-pill"><i class="fas fa-check"></i> Aman</span>
                                @endif
                            </li>
                        </ul>
                    </div>

                    {{-- Opsional: Tombol untuk melihat daftar UMKM yang bermasalah --}}
                    @if ($umkmPerluPerbaikan->isNotEmpty())
                        <div class="card-footer text-end pb-3">
                            <button type="button" class="btn btn-sm btn-warning-soft" data-bs-toggle="modal"
                                data-bs-target="#umkmQualityFixModal">
                                Perbaiki Data Sekarang <i class="fas fa-arrow-right ms-1"></i>
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <div class="card card-modern h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <span class="icon-circle icon-amber"><i class="fas fa-chart-pie"></i></span>
                            <h5 class="mb-0 header-title">Distribusi Kategori UMKM</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        @if ($kategoriStats->isEmpty())
                            <div class="text-muted text-center py-5 empty-state">Tidak ada data kategori.</div>
                        @else
                            <div class="chart-wrap chart-h-380">
                                <canvas id="kategoriDistributionChart"></canvas>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-lg-6 mb-4">
                <div class="card card-modern h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <span class="icon-circle icon-info"><i class="fas fa-location-dot"></i></span>
                            <h5 class="mb-0 header-title">Sebaran UMKM Berdasarkan Kelurahan/Kecamatan</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        @if (collect($wilayahValues)->sum() === 0)
                            <div class="text-muted text-center py-5 empty-state">Tidak ada data wilayah.</div>
                        @else
                            <div class="chart-wrap chart-h-340">
                                <canvas id="wilayahDistributionChart"></canvas>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4">
                <div class="card card-modern h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <span class="icon-circle icon-primary"><i class="fas fa-clock"></i></span>
                            <h5 class="mb-0 header-title">Karakteristik Waktu Operasional</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        @if (collect($jamBukaValues)->sum() === 0)
                            <div class="text-muted text-center py-5 empty-state">Tidak ada data jam buka.</div>
                        @else
                            <div class="chart-wrap chart-h-340">
                                <canvas id="jamBukaChart"></canvas>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Preview Foto Neo-Brutalism -->
        <div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true" style="z-index: 1070;">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content"
                    style="border: 4px solid #000; border-radius: 0; box-shadow: 10px 10px 0 #000; background-color: #f4f4f0;">

                    <!-- Header -->
                    <div
                        class="modal-header border-bottom-0 pb-0 pt-3 px-3 d-flex justify-content-between align-items-start">
                        <h5 class="modal-title text-uppercase" id="imagePreviewTitle"
                            style="color: #000; font-weight: 900; font-size: 1.1rem;">
                            Preview Foto
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                            style="background-color: #ff3838 !important; border: 3px solid #000 !important; border-radius: 0 !important; box-shadow: 3px 3px 0 #000 !important; opacity: 1 !important; padding: 0.5rem !important;">
                        </button>
                    </div>

                    <!-- Body (Tempat Foto Ditampilkan) -->
                    <div class="modal-body p-4 d-flex justify-content-center align-items-center">
                        <img id="imagePreviewTarget" src="" alt="Preview"
                            style="max-width: 100%; max-height: 70vh; object-fit: contain; border: 4px solid #000; box-shadow: 8px 8px 0 #000; background: #fff;">
                    </div>

                </div>
            </div>
        </div>

        <div class="modal fade" id="umkmQualityFixModal" tabindex="-1" aria-labelledby="umkmQualityFixModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                <div class="modal-content quality-modal">
                    <div class="modal-header quality-modal__header">
                        <div class="d-flex align-items-center gap-3">
                            <span class="quality-modal__icon"><i class="fas fa-screwdriver-wrench"></i></span>
                            <div>
                                <h5 class="modal-title mb-1" id="umkmQualityFixModalLabel">UMKM Perlu Perbaikan Data</h5>
                                <small class="quality-modal__subtitle">{{ $umkmPerluPerbaikan->count() }} data
                                    ditemukan</small>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-0">
                        @if ($umkmPerluPerbaikan->isEmpty())
                            <div class="p-4 text-center text-muted empty-state">
                                Semua data UMKM sudah lengkap.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 align-middle table-modern">
                                    <thead class="table-light">
                                        <tr>
                                            <th>UMKM</th>
                                            <th>Masalah</th>
                                            <th>Last Update</th>
                                            <th class="text-end">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($umkmPerluPerbaikan as $umkm)
                                            <tr>
                                                <td>
                                                    <div class="fw-semibold text-truncate umkm-name"
                                                        title="{{ $umkm->nama_umkm }}">
                                                        {{ \Illuminate\Support\Str::limit($umkm->nama_umkm, 30) }}
                                                    </div>
                                                    <small class="text-muted d-block text-truncate umkm-meta">
                                                        {{ optional($umkm->kategori)->nama_kategori ?? '-' }}
                                                    </small>
                                                </td>
                                                <td>
                                                    @if (empty($umkm->missing_fields))
                                                        <span class="text-muted">-</span>
                                                    @else
                                                        @foreach ($umkm->missing_fields as $field)
                                                            <span
                                                                class="badge bg-warning text-dark me-1 mb-1">{{ $field }}</span>
                                                        @endforeach
                                                    @endif
                                                </td>
                                                <td><small>{{ $umkm->updated_at?->format('d M Y') ?? '-' }}</small></td>
                                                <td class="text-end">
                                                    <a href="{{ route('admin.umkm.edit', $umkm) }}"
                                                        class="btn btn-sm btn-outline-primary">
                                                        Perbaiki
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    @vite('resources/css/admin/dashboard.css')
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        function openImagePreview(imageSrc, imageAlt) {
            // 1. Ambil elemen gambar dan judul di dalam modal
            const previewImage = document.getElementById('imagePreviewTarget');
            const previewTitle = document.getElementById('imagePreviewTitle');

            // 2. Timpa src dan alt dengan data dari gambar yang diklik
            previewImage.src = imageSrc;
            previewTitle.innerText = imageAlt;

            // 3. Tampilkan modal menggunakan bawaan Bootstrap
            const imageModal = new bootstrap.Modal(document.getElementById('imagePreviewModal'));
            imageModal.show();
        }
    </script>
    <script id="adminDashboardConfig" type="application/json">
        {!! json_encode([
            'ratingLabels' => $ratingCategoryLabels,
            'ratingValues' => $ratingCategoryValues,
            'kategoriLabels' => $kategoriStats->pluck('nama_kategori'),
            'kategoriValues' => $kategoriStats->pluck('umkm_count'),
            'wilayahLabels' => $wilayahLabels,
            'wilayahValues' => $wilayahValues,
            'jamBukaLabels' => $jamBukaLabels,
            'jamBukaValues' => $jamBukaValues,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>
    @vite('resources/js/refactor/admin-charts.js')
@endpush
