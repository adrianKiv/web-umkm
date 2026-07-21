<section class="mb-2">
    <!-- Area Header yang Bisa Diklik -->
    <div class="d-flex justify-content-between align-items-center mb-3" data-bs-toggle="collapse"
        data-bs-target="#collapseProfileInfo"
        aria-expanded="{{ $errors->has('name') || $errors->has('email') ? 'true' : 'false' }}" style="cursor: pointer;">
        <header>
            <h3 class="h5 fw-black text-uppercase text-dark mb-1">{{ __('Informasi Profil') }}</h3>
            <p class="fw-bold text-dark small mb-0">
                {{ __("Perbarui informasi profil dan alamat email akun Anda.") }}
            </p>
        </header>
        <button class="neo-btn-square" type="button" aria-label="Toggle">
            <i class="fas fa-chevron-down"></i>
        </button>
    </div>

    <!-- Form yang Disembunyikan -->
    <div class="collapse {{ $errors->has('name') || $errors->has('email') ? 'show' : '' }}" id="collapseProfileInfo">
        <div class="pt-4 mt-2 border-top border-dark border-4">
            <!-- Form Kirim Ulang Verifikasi (Hidden) -->
            <form id="send-verification" method="post" action="{{ route('verification.send') }}">
                @csrf
            </form>

            <form method="post" action="{{ route('profile.update') }}">
                @csrf
                @method('patch')

                <div class="mb-4">
                    <label for="name" class="form-label fw-black text-uppercase">{{ __('Nama Lengkap') }}</label>
                    <input id="name" name="name" type="text"
                        class="form-control neo-input @error('name') is-invalid @enderror"
                        value="{{ old('name', $user->name) }}"
                        required autofocus autocomplete="name" placeholder="Masukkan nama lengkap" />

                    @error('name')
                        <div class="text-danger fw-bold mt-2"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="email" class="form-label fw-black text-uppercase">{{ __('Email') }}</label>
                    <input id="email" name="email" type="email"
                        class="form-control neo-input @error('email') is-invalid @enderror"
                        value="{{ old('email', $user->email) }}" required autocomplete="username" placeholder="contoh: namasaya@email.com" />

                    @error('email')
                        <div class="text-danger fw-bold mt-2"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
                    @enderror

                    <!-- Peringatan Email Belum Verifikasi Gaya Neo -->
                    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
                        <div class="mt-3 p-3 bg-light-gray border border-dark border-3" style="box-shadow: 4px 4px 0 #000;">
                            <p class="fw-black text-dark mb-2 text-uppercase">
                                <i class="fas fa-exclamation-triangle text-danger me-2" style="-webkit-text-stroke: 1px #000;"></i>
                                {{ __('Alamat email Anda belum diverifikasi.') }}
                            </p>

                            <button form="send-verification" class="neo-btn-white py-1 px-3" style="font-size: 0.85rem;">
                                {{ __('KIRIM ULANG VERIFIKASI') }}
                            </button>

                            @if (session('status') === 'verification-link-sent')
                                <p class="fw-black text-success mt-2 mb-0 text-uppercase" style="-webkit-text-stroke: 0.5px #000; font-size: 0.85rem;">
                                    <i class="fas fa-check-circle me-1"></i> {{ __('Tautan verifikasi baru telah dikirim.') }}
                                </p>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="d-flex align-items-center gap-3 mt-4">
                    <button type="submit" class="neo-btn-green">{{ __('SIMPAN PROFIL') }}</button>

                    @if (session('status') === 'profile-updated')
                        <span class="neo-badge-success"><i class="fas fa-check me-1"></i>{{ __('Tersimpan.') }}</span>
                    @endif
                </div>
            </form>
        </div>
    </div>
</section>
