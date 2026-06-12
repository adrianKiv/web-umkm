@extends('admin.layout')

@section('title', 'ADMIN LOKASI - UMKM Kuliner')

@section('admin-content')
<div class="row">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Tambah Lokasi</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.lokasi.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Pilih Lokasi pada Peta <span class="text-danger">*</span></label>
                        <div
                            class="location-picker border rounded-3 p-3 bg-light"
                            data-location-picker
                            data-map-id="adminLokasiCreateMap"
                            data-latitude-input-id="adminLokasiLatitude"
                            data-longitude-input-id="adminLokasiLongitude"
                            data-readout-id="adminLokasiCoordinateReadout"
                            data-initial-latitude="{{ old('latitude', '-6.861082410263256') }}"
                            data-initial-longitude="{{ old('longitude', '107.59205888361987') }}"
                            data-initial-zoom="15"
                        >
                            <div id="adminLokasiCreateMap" data-location-picker-map class="map-h-360 rounded-12"></div>
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mt-3">
                                <small class="text-muted">Klik map atau geser marker untuk memilih koordinat.</small>
                                <small class="fw-semibold">Koordinat: <span id="adminLokasiCoordinateReadout">-</span></small>
                            </div>
                            <input type="hidden" id="adminLokasiLatitude" name="latitude" value="{{ old('latitude') }}">
                            <input type="hidden" id="adminLokasiLongitude" name="longitude" value="{{ old('longitude') }}">
                        </div>
                        @error('latitude')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        @error('longitude')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Simpan
                        </button>
                        <a href="{{ route('admin.lokasi.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @vite('resources/js/location-picker.js')
@endpush
