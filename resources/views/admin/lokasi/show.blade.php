@extends('admin.layout')

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
                    <div class="col-sm-3 fw-semibold">Nama Lokasi:</div>
                    <div class="col-sm-9">{{ $lokasi->nama_lokasi ?? '-' }}</div>
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
                    <div id="adminLokasiDetailMap" style="height: 340px; border-radius: 12px; border: 1px solid #e5e7eb;"></div>
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
                                <strong>{{ $umkm->nama_umkm }}</strong>
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
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (!window.L) return;

        const lat = {{ (float) $lokasi->latitude }};
        const lng = {{ (float) $lokasi->longitude }};
        const mapEl = document.getElementById('adminLokasiDetailMap');
        if (!mapEl) return;

        const map = L.map(mapEl).setView([lat, lng], 17);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        }).addTo(map);

        L.marker([lat, lng]).addTo(map).bindPopup('Lokasi ID {{ $lokasi->id_lokasi }}').openPopup();
    });
</script>
@endpush
