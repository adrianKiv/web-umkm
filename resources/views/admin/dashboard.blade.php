@extends('admin.layout')

@section('title', 'UMKM ADMIN')

@section('admin-content')
    <div class="row mb-4">
        <div class="col-md-12">
            <h2 class="mb-3">Admin Dashboard</h2>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-0">Total UMKM</h6>
                            <h3 class="mb-0">{{ $stats['total_umkm'] }}</h3>
                        </div>
                        <i class="fas fa-store fa-3x text-primary opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-0">Total Rating</h6>
                            <h3 class="mb-0">{{ $stats['total_rating'] }}</h3>
                        </div>
                        <i class="fas fa-star fa-3x text-warning opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-0">Rata-rata Rating</h6>
                            <h3 class="mb-0">{{ number_format($stats['avg_rating'], 1) }}</h3>
                        </div>
                        <i class="fas fa-chart-bar fa-3x text-success opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-0">Total Menu</h6>
                            <h3 class="mb-0">{{ $stats['total_menu'] }}</h3>
                        </div>
                        <i class="fas fa-users fa-3x text-info opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Data -->
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Distribusi Kategori UMKM</h5>
                    <i class="fas fa-chart-pie text-muted"></i>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-2.5">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-3">Kategori</th>
                                    <th class="text-center px-3">Jumlah UMKM</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($kategoriStats as $kategori)
                                    <tr>
                                        <td class="px-3 fw-medium">{{ $kategori->nama_kategori }}</td>
                                        <td class="text-center px-3">
                                            <span class="badge bg-primary rounded-pill">
                                                {{ $kategori->umkm_count }} UMKM
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted py-3">Tidak ada data kategori</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card border-warning shadow-sm h-100">
                <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Pengecekan Kualitas Data</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-0">
                        Beberapa data UMKM di bawah ini belum lengkap dan mungkin tidak akan tampil optimal di Peta.
                    </p>

                    <ul class="list-group list-group-flush">
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
                                <span class="badge bg-warning text-dark rounded-pill">{{ $umkmTanpaAlamat }} UMKM</span>
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
                                <span class="badge bg-warning text-dark rounded-pill">{{ $umkmTanpaTelepon }} UMKM</span>
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
                @if ($umkmTanpaKoordinat > 0 || $umkmTanpaTelepon > 0)
                    <div class="card-footer bg-white border-0 text-end pb-3">
                        <a href="{{ route('admin.umkm.index') }}" class="btn btn-sm btn-outline-warning text-dark">
                            Perbaiki Data Sekarang <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-user-check me-2 text-primary"></i>Konfirmasi Pengajuan Data UMKM</h5>
                    <span class="badge bg-primary">{{ $pendingSubmissions->count() }} pending</span>
                </div>
                <div class="card-body p-0">
                    @if($pendingSubmissions->isEmpty())
                        <div class="p-4 text-center text-muted">
                            Tidak ada pengajuan yang menunggu konfirmasi.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Pengusul</th>
                                        <th>Nama UMKM</th>
                                        <th>Kategori</th>
                                        <th>Kontak</th>
                                        <th>Menu Diajukan</th>
                                        <th>Koordinat</th>
                                        <th>Waktu</th>
                                        <th class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingSubmissions as $submission)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">{{ $submission->nama_pengusul }}</div>
                                                <small class="text-muted">{{ $submission->email_pengusul ?: '-' }}</small>
                                            </td>
                                            <td>
                                                <div class="fw-semibold">{{ $submission->nama_umkm }}</div>
                                                <small class="text-muted">{{ \Illuminate\Support\Str::limit($submission->alamat_lengkap, 45) }}</small>
                                            </td>
                                            <td>{{ $submission->kategori->nama_kategori ?? '-' }}</td>
                                            <td>{{ $submission->no_telfon }}</td>
                                            <td>
                                                @php
                                                    $menuCount = $submission->menuSubmissions->count();
                                                @endphp
                                                @if($menuCount > 0)
                                                    <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal"
                                                        data-bs-target="#submissionMenuDetailModal{{ $submission->id }}">
                                                        <i class="fas fa-utensils me-1"></i>{{ $menuCount }} Menu
                                                    </button>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <small>{{ $submission->latitude }}, {{ $submission->longitude }}</small>
                                            </td>
                                            <td><small>{{ $submission->created_at?->format('d M Y H:i') }}</small></td>
                                            <td class="text-end">
                                                <div class="d-flex justify-content-end gap-2">
                                                    <form action="{{ route('admin.submissions.approve', $submission) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn btn-sm btn-success"
                                                            onclick="return confirm('Setujui dan publikasikan UMKM ini?')">
                                                            <i class="fas fa-check me-1"></i>Terima
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('admin.submissions.reject', $submission) }}" method="POST" class="d-inline"
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

                        @foreach($pendingSubmissions as $submission)
                            @if($submission->menuSubmissions->count() > 0)
                                <div class="modal fade" id="submissionMenuDetailModal{{ $submission->id }}" tabindex="-1"
                                    aria-labelledby="submissionMenuDetailModalLabel{{ $submission->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="submissionMenuDetailModalLabel{{ $submission->id }}">
                                                    Menu Yang Diajukan Untuk {{ $submission->nama_umkm }}
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row g-2">
                                                    @foreach($submission->menuSubmissions as $menuSubmission)
                                                        <div class="col-md-6">
                                                            <div class="border rounded p-2 h-100 d-flex gap-2 align-items-start">
                                                                <img src="{{ $menuSubmission->foto_menu_url }}" alt="Foto {{ $menuSubmission->nama_menu }}"
                                                                    style="width: 56px; height: 56px; object-fit: cover; border-radius: 8px; border: 1px solid #dee2e6;"
                                                                    onerror="this.onerror=null;this.src='{{ asset('images/default-menu.svg') }}';">
                                                                <div>
                                                                    @if($menuSubmission->is_foto_daftar_menu)
                                                                        <div class="fw-semibold">Foto daftar menu</div>
                                                                        <small class="text-muted">Tanpa nama/harga menu</small>
                                                                    @else
                                                                        <div class="fw-semibold">{{ $menuSubmission->nama_menu }}</div>
                                                                        <small class="text-muted">Rp{{ number_format((float) $menuSubmission->harga_menu, 0, ',', '.') }}</small>
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
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-utensils me-2 text-primary"></i>Konfirmasi Pengajuan Data MENU</h5>
                    <span class="badge bg-primary">{{ $pendingMenuSubmissions->count() }} pending</span>
                </div>
                <div class="card-body p-0">
                    @if($pendingMenuSubmissions->isEmpty())
                        <div class="p-4 text-center text-muted">
                            Tidak ada pengajuan menu yang menunggu konfirmasi.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Menu</th>
                                        <th>Foto</th>
                                        <th>Pengusul</th>
                                        <th>UMKM Tujuan</th>
                                        <th>Waktu</th>
                                        <th class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingMenuSubmissions as $menuSubmission)
                                        <tr>
                                            <td>
                                                @if($menuSubmission->is_foto_daftar_menu)
                                                    <div class="fw-semibold">Foto daftar menu</div>
                                                    <small class="text-muted">Tanpa nama/harga menu</small>
                                                @else
                                                    <div class="fw-semibold">{{ $menuSubmission->nama_menu }}</div>
                                                    <small class="text-muted">Rp{{ number_format((float) $menuSubmission->harga_menu, 0, ',', '.') }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <img src="{{ $menuSubmission->foto_menu_url }}" alt="Foto {{ $menuSubmission->nama_menu }}"
                                                    style="width: 56px; height: 56px; object-fit: cover; border-radius: 8px; border: 1px solid #dee2e6;"
                                                    onerror="this.onerror=null;this.src='{{ asset('images/default-menu.svg') }}';">
                                            </td>
                                            <td>
                                                <div class="fw-semibold">{{ $menuSubmission->nama_pengusul ?: '-' }}</div>
                                                <small class="text-muted">{{ $menuSubmission->email_pengusul ?: '-' }}</small>
                                            </td>
                                            <td>{{ $menuSubmission->umkm->nama_umkm ?? '-' }}</td>
                                            <td><small>{{ $menuSubmission->created_at?->format('d M Y H:i') }}</small></td>
                                            <td class="text-end">
                                                <div class="d-flex justify-content-end gap-2">
                                                    <form action="{{ route('admin.menu-submissions.approve', $menuSubmission) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn btn-sm btn-success"
                                                            onclick="return confirm('Setujui pengajuan menu ini?')">
                                                            <i class="fas fa-check me-1"></i>Setujui
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('admin.menu-submissions.reject', $menuSubmission) }}" method="POST" class="d-inline"
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
@endsection
