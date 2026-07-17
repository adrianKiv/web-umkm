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
                            <i class="fas fa-lock fs-3"></i>
                        </div>
                        <h4 class="fw-bold text-dark">Konfirmasi Keamanan</h4>
                        <p class="text-muted small">
                            Ini adalah area aman dari aplikasi. Harap konfirmasi password Anda sebelum melanjutkan ke halaman berikutnya.
                        </p>
                    </div>

                    <!-- Form -->
                    <form method="POST" action="{{ route('password.confirm') }}">
                        @csrf

                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold">Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-key text-muted"></i>
                                </span>
                                <input id="password" type="password"
                                       class="form-control border-start-0 ps-0 bg-light @error('password') is-invalid @enderror"
                                       name="password" required autocomplete="current-password"
                                       placeholder="Masukkan password Anda">

                                @error('password')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary py-2 rounded-pill fw-bold">
                                <i class="fas fa-check-circle me-2"></i>Konfirmasi
                            </button>
                        </div>

                        <div class="text-center mt-3">
                            <a href="{{ url()->previous() }}" class="text-decoration-none text-muted small">
                                <i class="fas fa-arrow-left me-1"></i>Batal dan Kembali
                            </a>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>
@endsection
