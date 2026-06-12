@extends('admin.layout')

@section('title', 'ADMIN KATEGORI - UMKM Kuliner')

@section('admin-content')
<div class="row">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Detail Kategori</h5>
                <small class="text-muted">ID: {{ $kategori->id_kategori }}</small>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-3 fw-semibold">Nama Kategori:</div>
                    <div class="col-sm-9">{{ $kategori->nama_kategori }}</div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3 fw-semibold">Slug:</div>
                    <div class="col-sm-9">{{ $kategori->slug_kategori ?? '-' }}</div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3 fw-semibold">Kelompok:</div>
                    <div class="col-sm-9">
                        @if($kategori->kelompok)
                            <span class="badge bg-secondary">{{ $kategori->kelompok->nama_kelompok }}</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3 fw-semibold">Total UMKM:</div>
                    <div class="col-sm-9">
                        <span class="badge bg-info">{{ $kategori->umkms_count ?? 0 }}</span>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-sm-3 fw-semibold">Tanggal Dibuat:</div>
                    <div class="col-sm-9">{{ $kategori->created_at?->format('d M Y H:i') ?? '-' }}</div>
                </div>

                <!-- UMKM List -->
                @if(($kategori->umkms_count ?? 0) > 0)
                <hr>
                <h6 class="mb-3">UMKM dalam Kategori Ini</h6>
                <div class="list-group">
                    @foreach($kategori->umkms as $umkm)
                        <a href="{{ route('admin.umkm.show', $umkm) }}" class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between">
                                <strong title="{{ $umkm->nama_umkm }}">
                                    {{ \Illuminate\Support\Str::limit($umkm->nama_umkm, 30) }}
                                </strong>
                                <small class="text-muted">{{ $umkm->alamat_lengkap }}</small>
                            </div>
                        </a>
                    @endforeach
                </div>
                @endif

                <div class="d-flex align-items-center gap-2 mt-4">
                    <a href="{{ route('admin.kategori.edit', $kategori) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-1"></i>Edit
                    </a>
                    <a href="{{ route('admin.kategori.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Kembali
                    </a>
                    <form action="{{ route('admin.kategori.destroy', $kategori) }}" method="POST" class="mt-3">
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
