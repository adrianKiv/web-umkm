<section>
    <!-- Area Header yang Bisa Diklik -->
    <div class="d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#collapseUpdatePassword" aria-expanded="{{ $errors->updatePassword->isNotEmpty() ? 'true' : 'false' }}" style="cursor: pointer;">
        <header>
            <h4 class="h5 fw-bold text-dark mb-1">{{ __('Update Password') }}</h4>
            <p class="text-muted small mb-0">
                {{ __('Pastikan akun Anda menggunakan kata sandi yang panjang dan acak agar tetap aman.') }}
            </p>
        </header>
        <button class="btn btn-light rounded-circle shadow-sm" type="button" aria-label="Toggle">
            <i class="fas fa-chevron-down text-muted"></i>
        </button>
    </div>

    <!-- Form yang Disembunyikan -->
    <div class="collapse {{ $errors->updatePassword->isNotEmpty() ? 'show' : '' }}" id="collapseUpdatePassword">
        <div class="pt-4 mt-2 border-top">
            <form method="post" action="{{ route('password.update') }}">
                @csrf
                @method('put')

                <div class="mb-3">
                    <label for="update_password_current_password" class="form-label fw-semibold">{{ __('Current Password') }}</label>
                    <input id="update_password_current_password" name="current_password" type="password" class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" autocomplete="current-password" />
                    @error('current_password', 'updatePassword')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="update_password_password" class="form-label fw-semibold">{{ __('New Password') }}</label>
                    <input id="update_password_password" name="password" type="password" class="form-control @error('password', 'updatePassword') is-invalid @enderror" autocomplete="new-password" />
                    @error('password', 'updatePassword')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="update_password_password_confirmation" class="form-label fw-semibold">{{ __('Confirm Password') }}</label>
                    <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror" autocomplete="new-password" />
                    @error('password_confirmation', 'updatePassword')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex align-items-center gap-3 mt-4">
                    <button type="submit" class="btn btn-primary px-4 rounded-pill">{{ __('Save') }}</button>

                    @if (session('status') === 'password-updated')
                        <span class="text-success small fw-medium"><i class="fas fa-check-circle me-1"></i>{{ __('Saved.') }}</span>
                    @endif
                </div>
            </form>
        </div>
    </div>
</section>
