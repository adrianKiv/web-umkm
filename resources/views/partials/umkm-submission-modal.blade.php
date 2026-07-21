<style>
    .neo-input,
    .neo-select,
    .neo-textarea {
        border: 3px solid #000 !important;
        border-radius: 0 !important;
        box-shadow: 3px 3px 0 #000;
        transition: all 0.1s ease-in-out;
        background-color: #fff;
        color: #000;
        font-weight: 600;
    }

    /* Reaksi saat pengguna mengetik di dalam form */
    .neo-input:focus,
    .neo-select:focus,
    .neo-textarea:focus {
        outline: none !important;
        box-shadow: 4px 4px 0 #5ad641 !important;
        /* Bayangan hijau neon */
        transform: translate(-1px, -1px);
        border-color: #000 !important;
    }

    /* Label Form */
    .neo-form-label {
        font-weight: 900;
        text-transform: uppercase;
        font-size: 0.85rem;
        color: #000;
        margin-bottom: 6px;
        letter-spacing: 0.5px;
    }

    /* Kotak Penampung (Peta & Daftar Menu) */
    .neo-box {
        border: 3px solid #000;
        border-radius: 0;
        background-color: #f4f4f0;
        box-shadow: 4px 4px 0 #000;
        padding: 1rem;
    }

    /* Alert Error Form */
    .neo-alert-danger {
        background-color: #ff3838;
        color: #fff;
        border: 3px solid #000;
        box-shadow: 4px 4px 0 #000;
        font-weight: bold;
        border-radius: 0;
    }

    /* Tombol Hapus Menu */
    .neo-btn-danger {
        background: #ff3838;
        color: #fff;
        border: 3px solid #000;
        border-radius: 0;
        box-shadow: 3px 3px 0 #000;
        transition: transform 0.1s, box-shadow 0.1s;
        font-weight: bold;
    }

    .neo-btn-danger:active {
        transform: translate(3px, 3px);
        box-shadow: 0 0 0 #000;
    }

    /* Map override agar tidak melengkung */
    .neo-map-container {
        border: 3px solid #000 !important;
        border-radius: 0 !important;
    }

    .readonly-input[readonly] {
        background: #e9ecef;
        color: #888;
        border: 3px solid #000;
        font-weight: 600;
        opacity: 1;
        cursor: not-allowed;
    }
</style>

<div class="modal fade" id="umkmSubmissionModal" data-show-on-errors="{{ $errors->any() && old('nama_umkm') ? '1' : '0' }}"
    tabindex="-1" aria-labelledby="umkmSubmissionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">

        <form method="POST" action="{{ route('umkm-submissions.store') }}"
            class="neo-submit-form modal-content neo-modal border-0" novalidate enctype="multipart/form-data">
            @csrf

            <!-- Header Modal -->
            <div class="modal-header neo-modal-header border-bottom-0">
                <h5 class="modal-title neo-modal-title" id="umkmSubmissionModalLabel">
                    <i class="fas fa-bullhorn me-2 text-dark"></i> Ajukan UMKM Baru
                </h5>
                <button type="button" class="btn-close neo-btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>

            <!-- Body Modal -->
            <div class="modal-body p-4 bg-white">

                @if ($errors->any() && old('nama_umkm'))
                    <div class="neo-alert-danger p-3 mb-4">
                        <div class="text-uppercase mb-2"><i
                                class="fas fa-exclamation-triangle me-2"></i><strong>Terdapat Kesalahan:</strong></div>
                        <ul class="mb-0 ps-3" style="font-size: 0.9rem;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @php
                    $oldMenuNames = old('menu_nama', ['']);
                    $oldMenuPrices = old('menu_harga', ['']);
                    $maxMenuRows = max(count($oldMenuNames), count($oldMenuPrices), 1);
                @endphp

                <div class="row g-4">
                    <!-- Data Pengusul -->
                    <div class="col-md-6">
                        <label class="neo-form-label">
                            Nama Pengusul <span class="text-danger fs-5">*</span>
                        </label>

                        <input type="text" name="nama_pengusul"
                            class="form-control neo-input {{ auth()->check() ? 'readonly-input' : '' }}"
                            value="{{ old('nama_pengusul', auth()->user()?->name) }}" placeholder="Contoh: Adrian M"
                            required {{ auth()->check() ? 'readonly' : '' }}>
                        @error('nama_pengusul')
                            <div class="text-danger fw-bold mt-2 text-uppercase">
                                <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="neo-form-label">
                            Email Pengusul <span class="text-danger fs-5">*</span>
                        </label>

                        <input type="email" name="email_pengusul"
                            class="form-control neo-input {{ auth()->check() ? 'readonly-input' : '' }}"
                            value="{{ old('email_pengusul', auth()->user()?->email) }}"
                            placeholder="contoh: rian@email.com" required {{ auth()->check() ? 'readonly' : '' }}>
                        @error('email_pengusul')
                            <div class="text-danger fw-bold mt-2 text-uppercase">
                                <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Pemisah Visual -->
                    <div class="col-12">
                        <hr style="border-top: 3px dashed #000; opacity: 1;">
                    </div>

                    <!-- Data UMKM -->
                    <div class="col-md-6">
                        <label class="neo-form-label">Nama UMKM <span class="text-danger fs-5">*</span></label>
                        <input type="text" name="nama_umkm" class="form-control neo-input"
                            value="{{ old('nama_umkm') }}" placeholder="Contoh: Ayam Geprek Mantap" required>
                        @error('nama_umkm')
                            <div class="text-danger fw-bold mt-2 text-uppercase">
                                <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="neo-form-label">Kategori <span class="text-danger fs-5">*</span></label>
                        <select name="id_kategori" class="form-select neo-select" required>
                            <option value="">-- Pilih Kategori --</option>
                            @foreach ($kategoriList ?? collect() as $kategori)
                                <option value="{{ $kategori->id_kategori }}"
                                    {{ (string) old('id_kategori') === (string) $kategori->id_kategori ? 'selected' : '' }}>
                                    {{ $kategori->nama_kategori }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_kategori')
                            <div class="text-danger fw-bold mt-2 text-uppercase">
                                <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="neo-form-label">Jam Buka <span class="text-danger fs-5">*</span></label>
                        <input type="text" name="jam_buka" class="form-control neo-input"
                            value="{{ old('jam_buka') }}" placeholder="Contoh: 08:00 - 20:00" required>
                        @error('jam_buka')
                            <div class="text-danger fw-bold mt-2 text-uppercase">
                                <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="neo-form-label">No Telepon <span class="text-danger fs-5">*</span></label>
                        <input type="text" name="no_telfon" class="form-control neo-input"
                            value="{{ old('no_telfon') }}" placeholder="Contoh: 081234567890" required>
                        @error('no_telfon')
                            <div class="text-danger fw-bold mt-2 text-uppercase">
                                <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Lokasi Map -->
                    <div class="col-12 mt-4">
                        <label class="neo-form-label">Lokasi Peta <span class="text-danger fs-5">*</span></label>
                        <div class="neo-box" data-location-picker data-map-id="umkmSubmissionMap"
                            data-latitude-input-id="submissionLatitude" data-longitude-input-id="submissionLongitude"
                            data-readout-id="submissionCoordinateReadout"
                            data-initial-latitude="{{ old('latitude', '-6.861082410263256') }}"
                            data-initial-longitude="{{ old('longitude', '107.59205888361987') }}"
                            data-initial-zoom="15">

                            <div id="umkmSubmissionMap" data-location-picker-map
                                class="location-picker-map map-h-320 neo-map-container mb-3"></div>

                            <div
                                class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 px-1">
                                <small class="text-dark fw-bold"><i class="fas fa-hand-pointer me-1"></i> Geser marker
                                    /
                                    klik area map.</small>
                                <span class="badge"
                                    style="background: #e0e0e0; color: #000; border: 2px solid #000; font-size: 0.8rem; box-shadow: 2px 2px 0 #000;">
                                    📍 <span id="submissionCoordinateReadout">-</span>
                                </span>
                            </div>
                            <input type="hidden" id="submissionLatitude" name="latitude"
                                value="{{ old('latitude') }}" required>
                            <input type="hidden" id="submissionLongitude" name="longitude"
                                value="{{ old('longitude') }}" required>
                        </div>
                        @error('latitude')
                            <div class="text-danger fw-bold mt-2 text-uppercase">
                                <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                            </div>
                        @enderror
                        @error('longitude')
                            <div class="text-danger fw-bold mt-2 text-uppercase">
                                <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="neo-form-label">Alamat Lengkap <span class="text-danger fs-5">*</span></label>
                        <textarea name="alamat_lengkap" class="form-control neo-textarea" rows="2" required>{{ old('alamat_lengkap') }}</textarea>
                        @error('alamat_lengkap')
                            <div class="text-danger fw-bold mt-2 text-uppercase">
                                <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="neo-form-label">Deskripsi UMKM <span class="text-danger fs-5">*</span></label>
                        <textarea name="deskripsi" class="form-control neo-textarea" rows="3" required>{{ old('deskripsi') }}</textarea>
                        @error('deskripsi')
                            <div class="text-danger fw-bold mt-2 text-uppercase">
                                <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="neo-form-label">Foto UMKM <span class="text-danger fs-5">*</span></label>
                        <input type="file" name="foto_umkm" class="form-control neo-input"
                            style="padding-top: 0.35rem;" accept="image/*" required>
                        <small class="text-muted fw-bold mt-1 d-block">Format: JPG, JPEG, PNG, WEBP. Maks: 2MB.</small>
                        @error('foto_umkm')
                            <div class="text-danger fw-bold mt-2 text-uppercase">
                                <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Pemisah Visual -->
                    <div class="col-12">
                        <hr style="border-top: 3px dashed #000; opacity: 1;">
                    </div>
                    <label class="neo-form-label mb-0 fs-6">Isi salah satu, Daftar Menu atau Foto Daftar Menu</label>
                    <div class="col-12">
                        <hr style="border-top: 3px dashed #000; opacity: 1;">
                    </div>

                    <!-- Dynamic Menu List -->
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <label class="neo-form-label mb-0 fs-6">Daftar Menu <span
                                    class="text-muted fw-normal text-lowercase">(Opsional)</span></label>
                            <button type="button" class="neo-btn-outline py-1 px-3" style="font-size: 0.85rem;"
                                id="addSubmissionMenuItem">
                                <i class="fas fa-plus me-1"></i> Tambah
                            </button>
                        </div>

                        <div id="submissionMenuList" class="d-grid gap-3">
                            @for ($i = 0; $i < $maxMenuRows; $i++)
                                <div class="neo-box submission-menu-item p-3" data-menu-item>
                                    <div class="row g-3 align-items-end">
                                        <div class="col-md-4">
                                            <label class="neo-form-label" style="font-size: 0.75rem;">Nama
                                                Menu</label>
                                            <input type="text" name="menu_nama[]" class="form-control neo-input"
                                                value="{{ $oldMenuNames[$i] ?? '' }}" placeholder="Ayam Bakar">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="neo-form-label" style="font-size: 0.75rem;">Harga
                                                (Rp)</label>
                                            <input type="number" step="0.01" min="0" name="menu_harga[]"
                                                class="form-control neo-input" value="{{ $oldMenuPrices[$i] ?? '' }}"
                                                placeholder="25000">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="neo-form-label" style="font-size: 0.75rem;">Foto
                                                Menu</label>
                                            <input type="file" name="menu_foto[]" class="form-control neo-input"
                                                style="padding-top: 0.2rem;" accept="image/*">
                                        </div>
                                        <div class="col-md-1 d-grid">
                                            <button type="button" class="btn neo-btn-danger"
                                                style="padding: 0.45rem;" data-remove-menu-item title="Hapus menu">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endfor
                        </div>

                        <!-- Menu Errors -->
                        @if (
                            $errors->has('menu_nama') ||
                                $errors->has('menu_nama.*') ||
                                $errors->has('menu_harga') ||
                                $errors->has('menu_harga.*') ||
                                $errors->has('menu_foto') ||
                                $errors->has('menu_foto.*'))
                            <div class="neo-alert-danger p-2 mt-3 small">
                                @foreach (array_merge($errors->get('menu_nama'), $errors->get('menu_nama.*'), $errors->get('menu_harga'), $errors->get('menu_harga.*'), $errors->get('menu_foto'), $errors->get('menu_foto.*')) as $messages)
                                    @foreach ((array) $messages as $message)
                                        <div><i class="fas fa-times-circle me-1"></i> {{ $message }}</div>
                                    @endforeach
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="col-12 mt-4">
                        <label class="neo-form-label">Upload Foto Buku/Daftar Menu Lengkap <span
                                class="text-muted fw-normal text-lowercase">(Opsional)</span></label>
                        <input type="file" name="menu_daftar_foto[]" class="form-control neo-input"
                            style="padding-top: 0.35rem;" accept="image/*" multiple>
                        <small class="text-muted fw-bold mt-1 d-block">Gunakan jika malas menginput satu-persatu di
                            atas.</small>
                        @if ($errors->has('menu_daftar_foto') || $errors->has('menu_daftar_foto.*'))
                            <div class="neo-alert-danger p-2 mt-2 small">
                                @foreach (array_merge($errors->get('menu_daftar_foto'), $errors->get('menu_daftar_foto.*')) as $messages)
                                    @foreach ((array) $messages as $message)
                                        <div><i class="fas fa-times-circle me-1"></i> {{ $message }}</div>
                                    @endforeach
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Footer Modal -->
            <div class="modal-footer neo-modal-footer border-top-0 d-flex justify-content-between">
                <button type="button" class="neo-btn-outline m-0" data-bs-dismiss="modal">BATAL</button>
                <button type="submit" class="neo-btn-solid m-0">
                    <i class="fas fa-paper-plane me-2"></i>KIRIM PENGAJUAN
                </button>
            </div>

        </form>
    </div>
</div>

<!-- PERBAIKAN: Script agar Modal otomatis terbuka ulang jika ada error validasi -->
@if (
    $errors->has('nama_pengusul') ||
        $errors->has('email_pengusul') ||
        $errors->has('id_kategori') ||
        $errors->has('nama_umkm') ||
        $errors->has('jam_buka') ||
        $errors->has('no_telfon') ||
        $errors->has('latitude') ||
        $errors->has('longitude') ||
        $errors->has('alamat_lengkap') ||
        $errors->has('deskripsi') ||
        $errors->has('foto_umkm'))
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var ratingModal = new bootstrap.Modal(document.getElementById('umkmSubmissionModal'));
            ratingModal.show();
        });
    </script>
@endif
{{-- modal init and behaviors moved to resources/js/refactor/umkm-submission-modal.js --}}
<!-- Neo-Brutalism Loading Overlay -->
<div id="neoFormLoader" class="neo-loader-overlay d-none">
    <div class="neo-loader-box">
        <i class="fas fa-spinner fa-spin neo-loader-icon"></i>
        <h4 class="fw-black text-uppercase mt-3 mb-1" style="-webkit-text-stroke: 0.5px #000;">Memproses...</h4>
        <p class="fw-bold small mb-0">Mohon tunggu, data sedang dikirim.</p>
    </div>
</div>

@push('scripts')
    @vite(['resources/js/location-picker.js', 'resources/js/refactor/umkm-submission-modal.js'])
@endpush
