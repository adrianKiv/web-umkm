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
                            <i class="fas fa-envelope-circle-check fs-3"></i>
                        </div>
                        <h4 class="fw-bold text-dark">Verifikasi Email Anda</h4>
                        <p class="text-muted small">
                            Terima kasih telah mendaftar! Sebelum memulai, harap verifikasi alamat email Anda dengan mengeklik tautan yang baru saja kami kirimkan. Jika Anda tidak menerima email tersebut, kami akan dengan senang hati mengirimkan yang baru.
                        </p>
                    </div>

                    <!-- Notifikasi Sukses Mengirim Ulang -->
                    @if (session('status') == 'verification-link-sent')
                        <div class="alert alert-success alert-dismissible fade show small d-flex align-items-center" role="alert">
                            <i class="fas fa-check-circle fs-5 me-2"></i>
                            <div>Tautan verifikasi baru telah dikirim ke alamat email yang Anda berikan saat pendaftaran.</div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="d-flex flex-column gap-3 mt-4">
                        <!-- Form Kirim Ulang Email -->
                        <form method="POST" action="{{ route('verification.send') }}" class="w-100">
                            @csrf
                            <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill fw-bold">
                                <i class="fas fa-paper-plane me-2"></i>Kirim Ulang Email Verifikasi
                            </button>
                        </form>

                        <!-- Form Logout -->
                        <form method="POST" action="{{ route('logout') }}" class="w-100 text-center">
                            @csrf
                            <button type="submit" class="btn btn-link text-muted text-decoration-none small">
                                <i class="fas fa-sign-out-alt me-1"></i>Logout
                            </button>
                        </form>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
@endsection
