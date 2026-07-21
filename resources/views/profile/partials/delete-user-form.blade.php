<section class="mb-2">
    <header class="mb-4">
        <!-- Judul ditebalkan ekstrim dengan efek stroke -->
        <h3 class="fw-black text-uppercase text-danger" style="-webkit-text-stroke: 1px #000;">
            {{ __('Hapus Akun') }}
        </h3>
        <p class="fw-bold text-dark">
            {{ __('Setelah akun Anda dihapus, semua sumber daya dan datanya akan dihapus secara permanen.') }}
        </p>
    </header>

    <!-- Tombol Hapus Utama -->
    <button type="button" class="neo-btn-red" data-bs-toggle="modal" data-bs-target="#confirmUserDeletionModal">
        <i class="fas fa-trash-alt me-2"></i>{{ __('HAPUS AKUN') }}
    </button>

    <!-- Bootstrap Modal untuk Konfirmasi Hapus bergaya Neo -->
    <div class="modal fade" id="confirmUserDeletionModal" tabindex="-1" aria-labelledby="confirmUserDeletionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <!-- Menambahkan class neo-modal-content -->
            <div class="modal-content neo-modal-content">
                <form method="post" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('delete')

                    <!-- Header Modal -->
                    <div class="modal-header neo-modal-header">
                        <h5 class="modal-title fw-black text-uppercase text-danger" style="-webkit-text-stroke: 0.5px #000;" id="confirmUserDeletionModalLabel">
                            <i class="fas fa-exclamation-triangle me-2"></i>{{ __('Apakah Anda yakin?') }}
                        </h5>
                        <button type="button" class="neo-btn-square-close" data-bs-dismiss="modal" aria-label="Close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <!-- Body Modal -->
                    <div class="modal-body p-4 bg-light-gray">
                        <p class="fw-bold text-dark mb-4">
                            {{ __('Setelah akun dihapus, semua data akan hilang permanen. Masukkan password Anda untuk mengonfirmasi bahwa Anda ingin menghapus akun ini.') }}
                        </p>

                        <div class="mb-3">
                            <label for="password" class="fw-black text-uppercase mb-2">{{ __('Password') }}</label>
                            <input id="password" name="password" type="password"
                                class="form-control neo-input @error('password', 'userDeletion') is-invalid @enderror"
                                placeholder="{{ __('Masukkan Password') }}" />

                            @error('password', 'userDeletion')
                                <div class="text-danger fw-bold mt-2">
                                    <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <!-- Footer Modal -->
                    <div class="modal-footer neo-modal-footer">
                        <button type="button" class="neo-btn-white" data-bs-dismiss="modal">{{ __('BATAL') }}</button>
                        <button type="submit" class="neo-btn-red">{{ __('HAPUS PERMANEN') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Script agar modal tetap terbuka jika ada error (password salah) -->
@if($errors->userDeletion->isNotEmpty())
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var myModal = new bootstrap.Modal(document.getElementById('confirmUserDeletionModal'));
            myModal.show();
        });
    </script>
@endif

<!-- CSS Khusus untuk Modal Hapus Akun -->
<style>
    /* KOTAK MODAL UTAMA */
    .neo-modal-content {
        border: 4px solid #000;
        border-radius: 0;
        box-shadow: 12px 12px 0 #000;
        background: #fff;
        overflow: hidden;
    }

    /* HEADER KUNING PERINGATAN */
    .neo-modal-header {
        border-bottom: 4px solid #000;
        background: #ffde59;
        padding: 1rem 1.5rem;
    }

    /* FOOTER PUTIH */
    .neo-modal-footer {
        border-top: 4px solid #000;
        background: #fff;
        padding: 1.25rem 1.5rem;
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
    }

    .bg-light-gray {
        background-color: #f4f4f2;
    }

    /* TOMBOL MERAH (DANGER) NEO */
    .neo-btn-red {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #ff7675; /* Merah cerah retro */
        border: 3px solid #000;
        color: #000;
        font-weight: 900;
        text-transform: uppercase;
        padding: 0.8rem 1.5rem;
        box-shadow: 4px 4px 0 #000;
        transition: all 0.1s ease;
        cursor: pointer;
    }

    .neo-btn-red:hover {
        background: #d63031;
        color: #fff;
    }

    .neo-btn-red:active {
        transform: translate(4px, 4px);
        box-shadow: 0 0 0 #000;
    }

    /* INPUT PASSWORD NEO */
    .neo-input {
        border: 3px solid #000 !important;
        border-radius: 0 !important;
        padding: 0.8rem 1rem;
        font-weight: 600;
        color: #000;
        background: #fff;
        transition: all 0.1s ease;
    }

    .neo-input:focus {
        box-shadow: 4px 4px 0 #000;
        transform: translate(-2px, -2px);
        outline: none;
        border-color: #000;
    }

    .neo-input.is-invalid {
        border-color: #d63031 !important;
        background-color: #ffeaea;
        box-shadow: 4px 4px 0 #d63031;
    }
</style>
