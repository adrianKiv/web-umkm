<section>
    <!-- Area Header yang Bisa Diklik -->
    <div class="d-flex justify-content-between align-items-center" data-bs-toggle="collapse"
        data-bs-target="#collapseProfileInfo"
        aria-expanded="{{ $errors->has('name') || $errors->has('email') ? 'true' : 'false' }}" style="cursor: pointer;">
        <header>
            <h4 class="h5 fw-bold text-dark mb-1">{{ __('Profile Information') }}</h4>
            <p class="text-muted small mb-0">
                {{ __("
                Perbarui informasi profil dan alamat email akun Anda.") }}
            </p>
        </header>
        <button class="btn btn-light rounded-circle shadow-sm" type="button" aria-label="Toggle">
            <i class="fas fa-chevron-down text-muted"></i>
        </button>
    </div>

    <!-- Form yang Disembunyikan -->
    <div class="collapse {{ $errors->has('name') || $errors->has('email') ? 'show' : '' }}" id="collapseProfileInfo">
        <div class="pt-4 mt-2 border-top">
            <form id="send-verification" method="post" action="{{ route('verification.send') }}">
                @csrf
            </form>

            <form method="post" action="{{ route('profile.update') }}">
                @csrf
                @method('patch')

                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold">{{ __('Name') }}</label>
                    <input id="name" name="name" type="text"
                        class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}"
                        required autofocus autocomplete="name" />
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">{{ __('Email') }}</label>
                    <input id="email" name="email" type="email"
                        class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email', $user->email) }}" required autocomplete="username" />
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
                        <div class="mt-2">
                            <p class="small text-dark mb-1">
                                {{ __('Your email address is unverified.') }}
                                <button form="send-verification"
                                    class="btn btn-link p-0 m-0 align-baseline text-decoration-none small">
                                    {{ __('Click here to re-send the verification email.') }}
                                </button>
                            </p>

                            @if (session('status') === 'verification-link-sent')
                                <p class="small text-success fw-medium mt-1 mb-0">
                                    {{ __('A new verification link has been sent to your email address.') }}
                                </p>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="d-flex align-items-center gap-3 mt-4">
                    <button type="submit" class="btn btn-primary px-4 rounded-pill">{{ __('Save') }}</button>

                    @if (session('status') === 'profile-updated')
                        <span class="text-success small fw-medium"><i
                                class="fas fa-check-circle me-1"></i>{{ __('Saved.') }}</span>
                    @endif
                </div>
            </form>
        </div>
    </div>
</section>
