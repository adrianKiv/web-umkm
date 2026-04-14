@extends('admin.layout')

@section('admin-content')
<div class="row">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Detail UMKM</h5>
                <small class="text-muted">ID: {{ $umkm->id_umkm }}</small>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-3 fw-semibold">Nama UMKM:</div>
                    <div class="col-sm-9">{{ $umkm->nama_umkm }}</div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3 fw-semibold">Foto UMKM:</div>
                    <div class="col-sm-9">
                        <img src="{{ $umkm->foto_umkm_url }}" alt="Foto {{ $umkm->nama_umkm }}"
                             style="width: 220px; max-width: 100%; height: 140px; object-fit: cover; border-radius: 10px; border: 1px solid #dee2e6;"
                             onerror="this.onerror=null;this.src='{{ asset('images/default-umkm.svg') }}';">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3 fw-semibold">Slug:</div>
                    <div class="col-sm-9">{{ $umkm->slug_umkm ?? '-' }}</div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3 fw-semibold">Kategori:</div>
                    <div class="col-sm-9">
                        @if($umkm->kategori)
                            <span class="badge bg-info">{{ $umkm->kategori->nama_kategori }}</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3 fw-semibold">Jam Buka:</div>
                    <div class="col-sm-9">{{ $umkm->jam_buka }}</div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3 fw-semibold">No Telepon:</div>
                    <div class="col-sm-9">
                        @if($umkm->no_telfon)
                            <a href="tel:{{ $umkm->no_telfon }}">{{ $umkm->no_telfon }}</a>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3 fw-semibold">Alamat Lengkap:</div>
                    <div class="col-sm-9">{{ $umkm->alamat_lengkap }}</div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3 fw-semibold">Deskripsi:</div>
                    <div class="col-sm-9">{{ $umkm->deskripsi ?? '-' }}</div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3 fw-semibold">Lokasi:</div>
                    <div class="col-sm-9">
                        @if($umkm->lokasi)
                            {{ $umkm->lokasi->nama_lokasi ?? "{$umkm->lokasi->latitude}, {$umkm->lokasi->longitude}" }}
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3 fw-semibold">Rating Rata-rata:</div>
                    <div class="col-sm-9">
                        {{-- Gunakan pengecekan yang sama seperti fitur Ulasan --}}
                        @if($umkm->rating && $umkm->rating->count() > 0)
                            <span class="badge bg-success">{{ number_format($umkm->rating->avg('nilai_rating'), 1) }} / 5</span>
                            <small class="text-muted">({{ $umkm->rating->count() }} ulasan)</small>
                        @else
                            <span class="text-muted">Belum ada rating</span>
                        @endif
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3 fw-semibold">Tanggal Dibuat:</div>
                    <div class="col-sm-9">{{ $umkm->created_at?->format('d M Y H:i') ?? '-' }}</div>
                </div>

                <div class="row mb-4">
                    <div class="col-sm-3 fw-semibold">Terakhir Diperbarui:</div>
                    <div class="col-sm-9">{{ $umkm->updated_at?->format('d M Y H:i') ?? '-' }}</div>
                </div>

                <!-- Ratings Section -->
                @if($umkm->rating->count() > 0)
                <hr>
                <h6 class="mb-3">Ulasan Terbaru</h6>
                @foreach($umkm->rating->take(5) as $rating)
                    <div class="p-3 border rounded mb-2">
                        <div class="d-flex justify-content-between mb-2">
                            <strong>{{ $rating->nama_pengulas ?? 'Anonim' }}</strong>
                            <span class="badge bg-warning text-dark">
                                <i class="fas fa-star"></i> {{ $rating->nilai_rating }}
                            </span>
                        </div>
                        <p class="mb-2 text-muted">{{ $rating->created_at?->format('d M Y') }}</p>
                        <p class="mb-0">{{ $rating->komentar ?? '-' }}</p>
                    </div>
                @endforeach
                @endif

                <hr>
                <h6 class="mb-3">Daftar Menu</h6>
                @if($umkm->menu->count() > 0)
                    @foreach($umkm->menu as $menu)
                        <div class="p-2 border rounded mb-2 d-flex align-items-center gap-2">
                            <img src="{{ $menu->foto_menu_url }}" alt="Foto {{ $menu->nama_menu }}"
                                 style="width: 56px; height: 56px; object-fit: cover; border-radius: 8px; border: 1px solid #dee2e6;"
                                 onerror="this.onerror=null;this.src='{{ asset('images/default-menu.svg') }}';">
                            <div>
                                <div class="fw-semibold">{{ $menu->nama_menu }}</div>
                                <small class="text-muted">Rp{{ number_format((float) $menu->harga_menu, 0, ',', '.') }}</small>
                            </div>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted mb-0">Belum ada menu untuk UMKM ini.</p>
                @endif

                <div class="d-flex align-items-center gap-2 mt-4">
                    <a href="{{ route('admin.umkm.edit', $umkm) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-1"></i>Edit
                    </a>
                    <a href="{{ route('admin.umkm.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Kembali
                    </a>
                    <form action="{{ route('admin.umkm.destroy', $umkm) }}" method="POST" class="mt-3">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus?')">
                            <i class="fas fa-trash me-1"></i>Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
