@extends('admin.layout')

@section('title', 'ADMIN LOKASI - UMKM Kuliner')

@section('admin-content')
<div class="row">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Detail Lokasi</h5>
                <small class="text-muted">ID: {{ $lokasi->id_lokasi }}</small>
            </div>
            <div class="card-body">
<div class="row mb-3">
    <div class="col-sm-3 fw-semibold">UMKM terkait:</div>
    <div class="col-sm-9">
        @if ($lokasi->umkm)
            <span class="fw-bold text-dark" title="{{ $lokasi->umkm->nama_umkm }}">
                {{ \Illuminate\Support\Str::limit($lokasi->umkm->nama_umkm, 26) }}
            </span>
        @else
            <span class="text-muted fst-italic small">Belum terhubung</span>
        @endif
    </div>
</div>

                <div class="row mb-3">
                    <div class="col-sm-3 fw-semibold">Latitude:</div>
                    <div class="col-sm-9">
                        <code>{{ number_format($lokasi->latitude, 6) }}</code>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3 fw-semibold">Longitude:</div>
                    <div class="col-sm-9">
                        <code>{{ number_format($lokasi->longitude, 6) }}</code>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3 fw-semibold">Koordinat:</div>
                    <div class="col-sm-9">
                        <code>{{ $lokasi->latitude }}, {{ $lokasi->longitude }}</code>
                        <a href="https://maps.google.com/?q={{ $lokasi->latitude }},{{ $lokasi->longitude }}" target="_blank" class="ms-2">
                            <i class="fas fa-external-link-alt"></i> Buka di Google Maps
                        </a>
                    </div>
                </div>

                    <div class="mb-4">
                    <label class="form-label fw-semibold">Peta Lokasi</label>
                    <div id="adminLokasiDetailMap" class="map-h-340 rounded-12 border-light-200" data-latitude="{{ $lokasi->latitude }}" data-longitude="{{ $lokasi->longitude }}" data-lokasi-id="{{ $lokasi->id_lokasi }}"></div>
                </div>
{{--
                <div class="row mb-3">
                    <div class="col-sm-3 fw-semibold">Total UMKM:</div>
                    <div class="col-sm-9">
                        <span class="badge bg-info">{{ $lokasi->umkms_count ?? 0 }}</span>
                    </div>
                </div> --}}

                <div class="row mb-4">
                    <div class="col-sm-3 fw-semibold">Tanggal Dibuat:</div>
                    <div class="col-sm-9">{{ $lokasi->created_at?->format('d M Y H:i') ?? '-' }}</div>
                </div>

                <!-- UMKM List -->
                @if(($lokasi->umkms_count ?? 0) > 0)
                <hr>
                <h6 class="mb-3">UMKM dalam Lokasi Ini</h6>
                <div class="list-group">
                    @foreach($lokasi->umkms as $umkm)
                        <a href="{{ route('admin.umkm.show', $umkm) }}" class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between">
                                <strong title="{{ $umkm->nama_umkm }}">
                                    {{ \Illuminate\Support\Str::limit($umkm->nama_umkm, 30) }}
                                </strong>
                                <small class="text-muted">{{ $umkm->kategori?->nama_kategori }}</small>
                            </div>
                        </a>
                    @endforeach
                </div>
                @endif

                <div class="d-flex align-items-center gap-2 mt-4">
                    <a href="{{ route('admin.lokasi.edit', $lokasi) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-1"></i>Edit
                    </a>
                    <a href="{{ route('admin.lokasi.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Kembali
                    </a>
                    <form action="{{ route('admin.lokasi.destroy', $lokasi) }}" method="POST" class="mt-3">
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

@push('scripts')
    @vite('resources/js/refactor/admin-lokasi-map.js')
@endpush
