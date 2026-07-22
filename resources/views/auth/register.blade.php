@extends('auth.authlayouts')

@section('contentauth')

@section('title', 'Daftar - UMKM Kuliner')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;600;900&display=swap');

        /* NEO-BRUTALISM: LAYOUT & SHELL */
        .auth-shell {
            background-color: #e0e0e0 !important; /* Latar belakang abu-abu solid */
            background-image: radial-gradient(#94a3b8 1px, transparent 1px); /* Efek grid titik retro */
            background-size: 20px 20px;
            padding: 3rem 1.5rem 4rem;
            min-height: 100vh;
        }

        .auth-logo {
            display: none;
        }

        .auth-register {
            font-family: 'Space Grotesk', sans-serif;
            color: #000;
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
        }

        /* NEO-BRUTALISM: KOTAK UTAMA (CARD) */
        .auth-card {
            max-width: 980px !important;
            width: min(980px, 100%);
            margin: 0 auto;

            /* Sudut tajam, border tebal, shadow solid */
            border-radius: 0 !important;
            border: 4px solid #000 !important;
            box-shadow: 12px 12px 0 #000 !important;

            padding: 0 !important;
            background: #fff !important;
            display: flex;
            flex-direction: column;
        }

        .auth-card__inner {
            display: grid;
            grid-template-columns: minmax(0, 1.1fr) minmax(0, 1fr);
            min-height: 540px;
        }

        /* NEO-BRUTALISM: PANEL KIRI (INFO) */
        .auth-panel {
            padding: 2.8rem;
            background: #38bdf8; /* Biru Neo cerah membedakan dari Login yang Kuning */
            color: #000;
            border-right: 4px solid #000;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .auth-panel__badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.35rem 0.75rem;
            border: 3px solid #000;
            background: #fff;
            color: #000;
            font-size: 0.85rem;
            font-weight: 900;
            text-transform: uppercase;
            box-shadow: 3px 3px 0 #000;
            align-self: flex-start;
        }

        .auth-panel h1 {
            font-size: 2.2rem;
            font-weight: 900;
            line-height: 1.1;
            margin: 1.5rem 0 1rem;
            color: #000;
            text-transform: uppercase;
        }

        .auth-panel p {
            color: #000;
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 2rem;
            border-bottom: 3px solid #000;
            padding-bottom: 1rem;
        }

        .auth-panel__list {
            display: grid;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .auth-panel__item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1rem;
            font-weight: 900;
            color: #000;
        }

        .auth-panel__item i {
            color: #000;
            font-size: 1.2rem;
        }

        .auth-panel__meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-top: auto;
        }

        .auth-pill {
            padding: 0.4rem 0.75rem;
            border: 2px solid #000;
            background: #ffde59; /* Kuning cerah */
            color: #000;
            font-size: 0.75rem;
            font-weight: 900;
            text-transform: uppercase;
            box-shadow: 2px 2px 0 #000;
        }

        /* NEO-BRUTALISM: PANEL KANAN (FORM) */
        .auth-form {
            padding: 2.6rem;
            background: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .auth-form__header h2 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
            font-weight: 900;
            text-transform: uppercase;
            color: #000;
        }

        .auth-form__header p {
            color: #000;
            font-size: 0.95rem;
            font-weight: 600;
            margin-bottom: 2rem;
        }

        .auth-field {
            margin-bottom: 1.2rem;
        }

        .auth-label {
            display: block;
            font-weight: 900;
            font-size: 0.9rem;
            color: #000;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
        }

        .auth-input {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            background: #fff;
            border: 3px solid #000;
            border-radius: 0; /* Tanpa lengkungan */
            padding: 0.8rem 1rem;
            transition: all 0.1s ease;
        }

        .auth-input i {
            color: #000;
            font-size: 1.1rem;
        }

        .auth-input input {
            border: none;
            outline: none;
            background: transparent;
            width: 100%;
            font-size: 1rem;
            color: #000;
            font-weight: 600;
        }

        .auth-input:focus-within {
            box-shadow: 4px 4px 0 #000;
            background: #f4f4f2;
            transform: translate(-2px, -2px);
        }

        .auth-actions {
            margin: 1rem 0 2rem;
        }

        .auth-link {
            color: #000;
            font-weight: 900;
            text-decoration: underline;
            text-decoration-thickness: 2px;
            text-underline-offset: 4px;
        }

        .auth-link:hover {
            background: #38bdf8;
            text-decoration: none;
        }

        /* TOMBOL UTAMA */
        .auth-submit {
            width: 100%;
            border: 3px solid #000;
            border-radius: 0;
            padding: 1rem;
            background: #5ad641; /* Hijau neon */
            color: #000;
            font-weight: 900;
            font-size: 1.1rem;
            text-transform: uppercase;
            box-shadow: 4px 4px 0 #000;
            transition: all 0.1s ease;
            cursor: pointer;
        }

        .auth-submit:active {
            transform: translate(4px, 4px);
            box-shadow: 0 0 0 #000;
        }

        /* TOMBOL KEMBALI */
        .auth-back-btn {
            width: 100%;
            margin-top: 1rem;
            border: 3px solid #000;
            border-radius: 0;
            padding: 1rem;
            background: #fff;
            color: #000;
            font-weight: 900;
            font-size: 1rem;
            text-transform: uppercase;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            box-shadow: 4px 4px 0 #000;
            transition: all 0.1s ease;
        }

        .auth-back-btn:hover {
            background: #e0e0e0;
        }

        .auth-back-btn:active {
            transform: translate(4px, 4px);
            box-shadow: 0 0 0 #000;
        }

        .auth-footer {
            margin-top: 2rem;
            text-align: center;
            font-size: 0.95rem;
            color: #000;
            font-weight: 600;
            border-top: 3px solid #000;
            padding-top: 1.5rem;
        }

        /* RESPONSIVE UNTUK MOBILE */
        @media (max-width: 900px) {
            .auth-card__inner {
                grid-template-columns: 1fr;
                min-height: auto;
            }

            .auth-panel {
                border-right: none;
                border-bottom: 4px solid #000;
                order: 1;
            }

            .auth-form {
                order: 2;
                padding: 2rem;
            }
        }

        @media (max-width: 520px) {
            .auth-shell {
                padding: 1.5rem 1rem;
            }

            .auth-panel,
            .auth-form {
                padding: 1.5rem;
            }

            .auth-panel h1 {
                font-size: 1.8rem;
            }

            .auth-card {
                border-width: 3px !important;
                box-shadow: 8px 8px 0 #000 !important;
            }
        }
    </style>

    <x-slot name="title">
        Register - UMKM Kuliner
    </x-slot>

    <div class="auth-register">
        <div class="auth-card">
            <div class="auth-card__inner">
                <!-- SISI KIRI: PANEL INFO -->
                <section class="auth-panel">
                    <div class="auth-panel__badge">
                        <i class="fas fa-user-plus"></i> AKUN BARU
                    </div>
                    <h1>Buat akun untuk mulai jelajahi UMKM favoritmu.</h1>
                    <p>Registrasi cepat, simpan preferensi, dan dapatkan rekomendasi yang relevan.</p>

                    <div class="auth-panel__list">
                        <!-- Menggunakan ikon kotak (fa-check-square) agar lebih tegas/brutalist -->
                        <div class="auth-panel__item">
                            <i class="fas fa-check-square"></i> Daftar rekomendasi personal
                        </div>
                        <div class="auth-panel__item">
                            <i class="fas fa-check-square"></i> Simpan preferensi kategori
                        </div>
                        <div class="auth-panel__item">
                            <i class="fas fa-check-square"></i> Akses halaman profil dan pengaturan akun
                        </div>
                    </div>

                    <div class="auth-panel__meta">
                        <span class="auth-pill">Mudah</span>
                        <span class="auth-pill">Cepat</span>
                        <span class="auth-pill">Gratis</span>
                    </div>
                </section>

                <!-- SISI KANAN: FORM REGISTER -->
                <section class="auth-form">
                    <div class="auth-form__header">
                        <h2>Register Akun</h2>
                        <p>Isi data dasar untuk membuat akun baru.</p>
                    </div>

                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <div class="auth-field">
                            <label for="name" class="auth-label">Nama Lengkap</label>
                            <div class="auth-input">
                                <i class="fas fa-user"></i>
                                <input id="name" type="text" name="name" value="{{ old('name') }}" required
                                    autofocus autocomplete="name" placeholder="Nama lengkap" />
                            </div>
                            <x-input-error :messages="$errors->get('name')" class="mt-1 fw-bold text-danger" />
                        </div>

                        <div class="auth-field">
                            <label for="email" class="auth-label">Email</label>
                            <div class="auth-input">
                                <i class="fas fa-envelope"></i>
                                <input id="email" type="email" name="email" value="{{ old('email') }}" required
                                    autocomplete="username" placeholder="contoh: namasaya@email.com" />
                            </div>
                            <x-input-error :messages="$errors->get('email')" class="mt-1 fw-bold text-danger" />
                        </div>

                        <div class="auth-field">
                            <label for="password" class="auth-label">Password</label>
                            <div class="auth-input">
                                <i class="fas fa-lock"></i>
                                <input id="password" type="password" name="password" required
                                    autocomplete="new-password" placeholder="Minimal 8 karakter" />
                            </div>
                            <x-input-error :messages="$errors->get('password')" class="mt-1 fw-bold text-danger" />
                        </div>

                        <div class="auth-field">
                            <label for="password_confirmation" class="auth-label">Konfirmasi Password</label>
                            <div class="auth-input">
                                <i class="fas fa-lock"></i>
                                <input id="password_confirmation" type="password" name="password_confirmation" required
                                    autocomplete="new-password" placeholder="Ulangi password" />
                            </div>
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1 fw-bold text-danger" />
                        </div>

                        <button class="auth-submit mt-2" type="submit">BUAT AKUN</button>

                        <a class="auth-back-btn" href="{{ url('/') }}">
                            <i class="fas fa-arrow-left"></i> KEMBALI
                        </a>

                        <div class="auth-footer">
                            Sudah punya akun? <a class="auth-link" href="{{ route('login') }}">LOGIN SEKARANG</a>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </div>
@endsection
