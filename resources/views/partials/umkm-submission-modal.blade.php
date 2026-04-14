<div class="modal fade" id="umkmSubmissionModal" tabindex="-1" aria-labelledby="umkmSubmissionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">

        {{-- PERBAIKAN: Form digabung menjadi satu dengan modal-content --}}
        <form method="POST" action="{{ route('umkm-submissions.store') }}" class="modal-content" enctype="multipart/form-data">
            @csrf

            <div class="modal-header">
                <h5 class="modal-title" id="umkmSubmissionModalLabel">
                    <i class="fas fa-plus-circle me-2 text-primary"></i>Ajukan Data UMKM Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                @if ($errors->any() && old('nama_umkm'))
                    <div class="alert alert-danger py-2 mb-3">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nama Pengusul <span class="text-danger">*</span></label>
                        <input type="text" name="nama_pengusul" class="form-control" value="{{ old('nama_pengusul') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email Pengusul</label>
                        <input type="email" name="email_pengusul" class="form-control" value="{{ old('email_pengusul') }}" placeholder="opsional">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Nama UMKM <span class="text-danger">*</span></label>
                        <input type="text" name="nama_umkm" class="form-control" value="{{ old('nama_umkm') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select name="id_kategori" class="form-select" required>
                            <option value="">Pilih kategori</option>
                            @foreach(($kategoriList ?? collect()) as $kategori)
                                <option value="{{ $kategori->id_kategori }}" {{ (string) old('id_kategori') === (string) $kategori->id_kategori ? 'selected' : '' }}>
                                    {{ $kategori->nama_kategori }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Jam Buka <span class="text-danger">*</span></label>
                        <input type="text" name="jam_buka" class="form-control" value="{{ old('jam_buka') }}" placeholder="contoh: 08:00-20:00" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">No Telepon <span class="text-danger">*</span></label>
                        <input type="text" name="no_telfon" class="form-control" value="{{ old('no_telfon') }}" placeholder="contoh: 081234567890" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Pilih Lokasi pada Peta <span class="text-danger">*</span></label>
                        <div
                            class="location-picker border rounded-3 p-3 bg-light"
                            data-location-picker
                            data-map-id="umkmSubmissionMap"
                            data-latitude-input-id="submissionLatitude"
                            data-longitude-input-id="submissionLongitude"
                            data-readout-id="submissionCoordinateReadout"
                            data-initial-latitude="{{ old('latitude', '-6.861082410263256') }}"
                            data-initial-longitude="{{ old('longitude', '107.59205888361987') }}"
                            data-initial-zoom="15"
                        >
                            <div id="umkmSubmissionMap" data-location-picker-map style="height: 320px; border-radius: 12px;"></div>
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mt-3">
                                <small class="text-muted">Geser marker atau klik map untuk menentukan koordinat.</small>
                                <small class="fw-semibold">Koordinat: <span id="submissionCoordinateReadout">-</span></small>
                            </div>
                            <input type="hidden" id="submissionLatitude" name="latitude" value="{{ old('latitude') }}">
                            <input type="hidden" id="submissionLongitude" name="longitude" value="{{ old('longitude') }}">
                        </div>
                        @error('latitude')<span class="text-danger small d-block mt-1">{{ $message }}</span>@enderror
                        @error('longitude')<span class="text-danger small d-block mt-1">{{ $message }}</span>@enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
                        <textarea name="alamat_lengkap" class="form-control" rows="2" required>{{ old('alamat_lengkap') }}</textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Deskripsi UMKM <span class="text-danger">*</span></label>
                        <textarea name="deskripsi" class="form-control" rows="3" required>{{ old('deskripsi') }}</textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Foto UMKM</label>
                        <input type="file" name="foto_umkm" class="form-control" accept="image/*">
                        <small class="text-muted">Format: JPG, JPEG, PNG, WEBP. Maksimal 2MB.</small>
                        @error('foto_umkm')<span class="text-danger small d-block mt-1">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane me-1"></i>Kirim Pengajuan
                </button>
            </div>

        </form>
    </div>
</div>

@if ($errors->any() && old('nama_umkm'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modalEl = document.getElementById('umkmSubmissionModal');
            if (!modalEl) return;
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        });
    </script>
@endif

@push('styles')
    <style>
        .location-picker-map {
            min-height: 320px;
        }
    </style>
@endpush

@push('scripts')
    @vite('resources/js/location-picker.js')
@endpush
