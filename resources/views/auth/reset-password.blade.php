@extends('auth.authlayouts')

@section('contentauth')
<div class="container py-5">
    <div class="row justify-content-center align-items-center" style="min-height: 60vh;">
        <div class="col-12 col-md-8 col-lg-5">

            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-body p-4 p-md-5">

                    <!-- Header -->
                    <div class="text-center mb-4">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <i class="fas fa-unlock-alt fs-3"></i>
                        </div>
                        <h4 class="fw-bold text-dark">Buat Password Baru</h4>
                        <p class="text-muted small">
                            Silakan masukkan alamat email Anda beserta password baru yang ingin digunakan.
                        </p>
                    </div>

                    <!-- Form -->
                    <form method="POST" action="{{ route('password.store') }}">
                        @csrf

                        <!-- Password Reset Token (Wajib ada dan tersembunyi) -->
                        <input type="hidden" name="token" value="{{ $request->route('token') }}">

                        <!-- Alamat Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Alamat Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-at text-muted"></i>
                                </span>
                                <input id="email" type="email"
                                       class="form-control border-start-0 ps-0 bg-light @error('email') is-invalid @enderror"
                                       name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username"
                                       placeholder="Masukkan email terdaftar Anda">

                                @error('email')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <!-- Password Baru -->
                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">Password Baru</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-lock text-muted"></i>
                                </span>
                                <input id="password" type="password"
                                       class="form-control border-start-0 ps-0 bg-light @error('password') is-invalid @enderror"
                                       name="password" required autocomplete="new-password"
                                       placeholder="Buat password baru">

                                @error('password')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <!-- Konfirmasi Password Baru -->
                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label fw-semibold">Konfirmasi Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-check-circle text-muted"></i>
                                </span>
                                <input id="password_confirmation" type="password"
                                       class="form-control border-start-0 ps-0 bg-light @error('password_confirmation') is-invalid @enderror"
                                       name="password_confirmation" required autocomplete="new-password"
                                       placeholder="Ketik ulang password baru">

                                @error('password_confirmation')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <!-- Tombol Simpan -->
                        <div class="d-grid mt-2">
                            <button type="submit" class="btn btn-primary py-2 rounded-pill fw-bold">
                                <i class="fas fa-save me-2"></i>Simpan Password Baru
                            </button>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>
@endsection
