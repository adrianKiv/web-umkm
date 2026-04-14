@extends('admin.layout')

@section('admin-content')
<div class="row">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Edit UMKM</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.umkm.update', $umkm) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="nama_umkm" class="form-label">Nama UMKM <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nama_umkm') is-invalid @enderror"
                               id="nama_umkm" name="nama_umkm" value="{{ old('nama_umkm', $umkm->nama_umkm) }}" required>
                        @error('nama_umkm')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="slug_umkm" class="form-label">Slug</label>
                        <input type="text" class="form-control @error('slug_umkm') is-invalid @enderror"
                               id="slug_umkm" name="slug_umkm" value="{{ old('slug_umkm', $umkm->slug_umkm) }}">
                        @error('slug_umkm')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="jam_buka" class="form-label">Jam Buka <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('jam_buka') is-invalid @enderror"
                               id="jam_buka" name="jam_buka" value="{{ old('jam_buka', $umkm->jam_buka) }}" required>
                        @error('jam_buka')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="no_telfon" class="form-label">No Telepon</label>
                        <input type="text" class="form-control @error('no_telfon') is-invalid @enderror"
                               id="no_telfon" name="no_telfon" value="{{ old('no_telfon', $umkm->no_telfon) }}">
                        @error('no_telfon')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="alamat_lengkap" class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('alamat_lengkap') is-invalid @enderror"
                                  id="alamat_lengkap" name="alamat_lengkap" rows="3" required>{{ old('alamat_lengkap', $umkm->alamat_lengkap) }}</textarea>
                        @error('alamat_lengkap')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-control @error('deskripsi') is-invalid @enderror"
                                  id="deskripsi" name="deskripsi" rows="3">{{ old('deskripsi', $umkm->deskripsi) }}</textarea>
                        @error('deskripsi')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="foto_umkm" class="form-label">Foto UMKM</label>
                        <input type="file" class="form-control @error('foto_umkm') is-invalid @enderror"
                               id="foto_umkm" name="foto_umkm" accept="image/*">
                        <small class="text-muted d-block mb-2">Kosongkan jika tidak ingin mengganti foto. Maksimal 2MB.</small>
                        <img src="{{ $umkm->foto_umkm_url }}" alt="Foto {{ $umkm->nama_umkm }}"
                             style="width: 140px; height: 100px; object-fit: cover; border-radius: 8px; border: 1px solid #dee2e6;"
                             onerror="this.onerror=null;this.src='{{ asset('images/default-umkm.svg') }}';">
                        @error('foto_umkm')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="id_kategori" class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select class="form-select @error('id_kategori') is-invalid @enderror"
                                id="id_kategori" name="id_kategori" required>
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($kategoris as $kategori)
                                <option value="{{ $kategori->id_kategori }}" {{ old('id_kategori', $umkm->id_kategori) == $kategori->id_kategori ? 'selected' : '' }}>
                                    {{ $kategori->nama_kategori }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_kategori')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Lokasi UMKM <span class="text-danger">*</span></label>
                        <div
                            class="location-picker border rounded-3 p-3 bg-light"
                            data-location-picker
                            data-map-id="adminUmkmEditMap"
                            data-latitude-input-id="adminUmkmEditLatitude"
                            data-longitude-input-id="adminUmkmEditLongitude"
                            data-readout-id="adminUmkmEditCoordinateReadout"
                            data-initial-latitude="{{ old('latitude', optional($umkm->lokasi)->latitude ?? '-6.861082410263256') }}"
                            data-initial-longitude="{{ old('longitude', optional($umkm->lokasi)->longitude ?? '107.59205888361987') }}"
                            data-initial-zoom="16"
                        >
                            <div id="adminUmkmEditMap" data-location-picker-map style="height: 340px; border-radius: 12px;"></div>
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mt-3">
                                <small class="text-muted">Klik map atau geser marker untuk memperbarui koordinat lokasi UMKM.</small>
                                <small class="fw-semibold">Koordinat: <span id="adminUmkmEditCoordinateReadout">-</span></small>
                            </div>
                            <input type="hidden" id="adminUmkmEditLatitude" name="latitude" value="{{ old('latitude', optional($umkm->lokasi)->latitude) }}">
                            <input type="hidden" id="adminUmkmEditLongitude" name="longitude" value="{{ old('longitude', optional($umkm->lokasi)->longitude) }}">
                        </div>
                        @error('latitude')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        @error('longitude')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Update
                        </button>
                        <a href="{{ route('admin.umkm.show', $umkm) }}" class="btn btn-secondary">
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
