<section class="mb-2">
    <!-- Area Header yang Bisa Diklik -->
    <div class="d-flex justify-content-between align-items-center mb-3" data-bs-toggle="collapse" data-bs-target="#collapseUpdatePassword" aria-expanded="{{ $errors->updatePassword->isNotEmpty() ? 'true' : 'false' }}" style="cursor: pointer;">
        <header>
            <h3 class="h5 fw-black text-uppercase text-dark mb-1">{{ __('Ganti Password') }}</h3>
            <p class="fw-bold text-dark small mb-0">
                {{ __('Perbarui kata sandi akun Anda dengan kata sandi yang kuat.') }}
            </p>
        </header>
        <button class="neo-btn-square" type="button" aria-label="Toggle">
            <i class="fas fa-chevron-down"></i>
        </button>
    </div>

    <!-- Form yang Disembunyikan -->
    <div class="collapse {{ $errors->updatePassword->isNotEmpty() ? 'show' : '' }}" id="collapseUpdatePassword">
        <div class="pt-4 mt-2 border-top border-dark border-4">
            <form method="post" action="{{ route('password.update') }}">
                @csrf
                @method('put')

                <div class="mb-4">
                    <label for="update_password_current_password" class="form-label fw-black text-uppercase">{{ __('Password Sekarang') }}</label>
                    <input id="update_password_current_password" name="current_password" type="password"
                        class="form-control neo-input @error('current_password', 'updatePassword') is-invalid @enderror"
                        autocomplete="current-password" placeholder="Masukkan password saat ini" />

                    @error('current_password', 'updatePassword')
                        <div class="text-danger fw-bold mt-2"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="update_password_password" class="form-label fw-black text-uppercase">{{ __('Password Baru') }}</label>
                    <input id="update_password_password" name="password" type="password"
                        class="form-control neo-input @error('password', 'updatePassword') is-invalid @enderror"
                        autocomplete="new-password" placeholder="Minimal 8 karakter" />

                    @error('password', 'updatePassword')
                        <div class="text-danger fw-bold mt-2"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="update_password_password_confirmation" class="form-label fw-black text-uppercase">{{ __('Konfirmasi Password') }}</label>
                    <input id="update_password_password_confirmation" name="password_confirmation" type="password"
                        class="form-control neo-input @error('password_confirmation', 'updatePassword') is-invalid @enderror"
                        autocomplete="new-password" placeholder="Ulangi password baru" />

                    @error('password_confirmation', 'updatePassword')
                        <div class="text-danger fw-bold mt-2"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex align-items-center gap-3 mt-4">
                    <button type="submit" class="neo-btn-green">{{ __('SIMPAN PASSWORD') }}</button>

                    @if (session('status') === 'password-updated')
                        <span class="neo-badge-success"><i class="fas fa-check me-1"></i>{{ __('Tersimpan.') }}</span>
                    @endif
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Tambahan CSS (Jika belum ada di global file) -->
<style>
    /* TOMBOL KOTAK KECIL (UNTUK TOGGLE) */
    .neo-btn-square {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        background: #fff;
        border: 3px solid #000;
        color: #000;
        font-size: 1rem;
        box-shadow: 3px 3px 0 #000;
        cursor: pointer;
        transition: all 0.1s ease;
    }

    .neo-btn-square:active {
        transform: translate(3px, 3px);
        box-shadow: 0 0 0 #000;
    }

    /* BADGE SUKSES NEO */
    .neo-badge-success {
        display: inline-flex;
        align-items: center;
        background: #5ad641;
        color: #000;
        border: 2px solid #000;
        padding: 0.4rem 0.8rem;
        font-weight: 900;
        text-transform: uppercase;
        font-size: 0.85rem;
        box-shadow: 2px 2px 0 #000;
    }

    /* Memutar icon chevron saat collapse terbuka */
    [aria-expanded="true"] .neo-btn-square i {
        transform: rotate(180deg);
        transition: transform 0.2s ease;
    }

    [aria-expanded="false"] .neo-btn-square i {
        transform: rotate(0deg);
        transition: transform 0.2s ease;
    }
</style>
