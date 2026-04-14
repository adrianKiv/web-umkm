@extends('admin.layout')

@section('admin-content')
<div class="row">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Detail Kelompok</h5>
                <small class="text-muted">ID: {{ $kelompok->id_kelompok }}</small>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-3 fw-semibold">Nama Kelompok:</div>
                    <div class="col-sm-9">{{ $kelompok->nama_kelompok }}</div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3 fw-semibold">Total Kategori:</div>
                    <div class="col-sm-9">
                        <span class="badge bg-info">{{ $kelompok->kategoris_count ?? 0 }}</span>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-sm-3 fw-semibold">Tanggal Dibuat:</div>
                    <div class="col-sm-9">{{ $kelompok->created_at?->format('d M Y H:i') ?? '-' }}</div>
                </div>

                <!-- Kategori List -->
                @if(($kelompok->kategoris_count ?? 0) > 0)
                <hr>
                <h6 class="mb-3">Kategori dalam Kelompok Ini</h6>
                <div class="list-group">
                    @foreach($kelompok->kategoris as $kategori)
                        <a href="{{ route('admin.kategori.show', $kategori) }}" class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between">
                                <strong>{{ $kategori->nama_kategori }}</strong>
                                <small class="badge bg-secondary">{{ $kategori->umkms_count ?? 0 }} UMKM</small>
                            </div>
                        </a>
                    @endforeach
                </div>
                @endif

                <div class="d-flex align-items-center gap-2 mt-4">
                    <a href="{{ route('admin.kelompok.edit', $kelompok) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-1"></i>Edit
                    </a>
                    <a href="{{ route('admin.kelompok.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Kembali
                    </a>
                    <form action="{{ route('admin.kelompok.destroy', $kelompok) }}" method="POST" class="mt-3">
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
