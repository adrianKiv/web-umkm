@extends('admin.layout')

@section('title', 'ADMIN RATING - UMKM Kuliner')

@section('admin-content')
<div class="row">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Detail Rating/Ulasan</h5>
                <small class="text-muted">ID: {{ $rating->id_rating }}</small>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-3 fw-semibold">UMKM:</div>
                    <div class="col-sm-9">
                        <a href="{{ route('admin.umkm.show', $rating->umkm) }}">
                            {{ $rating->umkm->nama_umkm }}
                        </a>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3 fw-semibold">Rating:</div>
                    <div class="col-sm-9">
                        <span class="badge bg-warning text-dark fs-6 py-2 px-3">
                            <i class="fas fa-star"></i> {{ $rating->nilai_rating }}/5
                        </span>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3 fw-semibold">Nama Reviewer:</div>
                    <div class="col-sm-9">{{ $rating->nama_pengulas ?? 'Anonim' }}</div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3 fw-semibold">Ulasan:</div>
                    <div class="col-sm-9">
                        <div class="p-3 bg-light rounded">
                            {{ $rating->komentar ?? '-' }}
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-sm-3 fw-semibold">Tanggal:</div>
                    <div class="col-sm-9">
                        {{ $rating->created_at?->format('l, d M Y H:i') ?? '-' }}
                    </div>
                </div>

                <!-- UMKM Info -->
                <hr>
                <h6 class="mb-3">Informasi UMKM</h6>
                <div class="row">
                    <div class="col-sm-3 fw-semibold">Alamat:</div>
                    <div class="col-sm-9">{{ $rating->umkm->alamat_lengkap }}</div>
                </div>
                <div class="row">
                    <div class="col-sm-3 fw-semibold">Kategori:</div>
                    <div class="col-sm-9">
                        @if($rating->umkm->kategori)
                            <span class="badge bg-info">{{ $rating->umkm->kategori->nama_kategori }}</span>
                        @endif
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 mt-4">
                    <a href="{{ route('admin.rating.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Kembali
                    </a>
                    <form action="{{ route('admin.rating.destroy', $rating) }}" method="POST" class="mt-3">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus ulasan ini?')">
                            <i class="fas fa-trash me-1"></i>Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
