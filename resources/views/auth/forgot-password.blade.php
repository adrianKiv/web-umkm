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
                            <i class="fas fa-envelope-open-text fs-3"></i>
                        </div>
                        <h4 class="fw-bold text-dark">Lupa Password?</h4>
                        <p class="text-muted small">
                            Tidak masalah. Cukup beri tahu kami alamat email Anda, dan kami akan mengirimkan tautan untuk membuat password baru.
                        </p>
                    </div>

                    <!-- Notifikasi Sukses Mengirim Link (Session Status) -->
                    @if (session('status'))
                        <div class="alert alert-success alert-dismissible fade show small d-flex align-items-center" role="alert">
                            <i class="fas fa-check-circle fs-5 me-2"></i>
                            <div>{{ session('status') }}</div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Form -->
                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf

                        <div class="mb-4">
                            <label for="email" class="form-label fw-semibold">Alamat Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-at text-muted"></i>
                                </span>
                                <input id="email" type="email"
                                       class="form-control border-start-0 ps-0 bg-light @error('email') is-invalid @enderror"
                                       name="email" value="{{ old('email') }}" required autofocus
                                       placeholder="Masukkan email terdaftar Anda">

                                @error('email')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary py-2 rounded-pill fw-bold">
                                <i class="fas fa-paper-plane me-2"></i>Kirim Link Reset
                            </button>
                        </div>

                        <!-- Link Kembali -->
                        <div class="text-center mt-3">
                            <a href="{{ route('login') }}" class="text-decoration-none text-muted small">
                                <i class="fas fa-arrow-left me-1"></i>Kembali ke Halaman Login
                            </a>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>
@endsection
