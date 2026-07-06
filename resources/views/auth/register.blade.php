<x-guest-layout>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap');

        .auth-shell {
            background: radial-gradient(900px 520px at 85% 10%, rgba(219, 234, 254, 0.85) 0%, rgba(219, 234, 254, 0) 65%),
                radial-gradient(800px 420px at 5% 20%, rgba(236, 253, 245, 0.9) 0%, rgba(236, 253, 245, 0) 70%),
                #f8fafc !important;
            padding: 3rem 1.5rem 4rem;
        }

        .auth-logo {
            display: none;
        }

        .auth-card {
            max-width: 980px !important;
            width: min(980px, 100%);
            border-radius: 24px !important;
            margin: 0 auto;
            border: 1px solid rgba(148, 163, 184, 0.3) !important;
            box-shadow: 0 28px 60px rgba(15, 23, 42, 0.15) !important;
            padding: 0 !important;
            overflow: hidden;
            background: #3c87d3 !important;
        }

        .auth-register {
            font-family: 'Space Grotesk', sans-serif;
            color: #0f172a;
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
        }

        .auth-card__inner {
            display: grid;
            grid-template-columns: minmax(0, 1.1fr) minmax(0, 1fr);
            min-height: 540px;
        }

        .auth-panel {
            padding: 2.8rem;
            background: linear-gradient(135deg, #1d4ed8 0%, #0f172a 70%);
            color: #e2e8f0;
            position: relative;
            overflow: hidden;
        }

        .auth-panel::after {
            content: '';
            position: absolute;
            right: -120px;
            top: -120px;
            width: 260px;
            height: 260px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.45) 0%, rgba(59, 130, 246, 0) 70%);
        }

        .auth-panel__badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.35rem 0.75rem;
            border-radius: 999px;
            background: rgba(59, 130, 246, 0.18);
            color: #bfdbfe;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .auth-panel h1 {
            font-size: 2rem;
            line-height: 1.2;
            margin: 1rem 0 0.75rem;
            color: #529feb;
        }

        .auth-panel p {
            color: rgba(226, 232, 240, 0.85);
            font-size: 0.95rem;
            margin-bottom: 1.5rem;
        }

        .auth-panel__list {
            display: grid;
            gap: 0.75rem;
            margin-bottom: 1.75rem;
        }

        .auth-panel__item {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            font-size: 0.9rem;
        }

        .auth-panel__item i {
            color: #93c5fd;
        }

        .auth-panel__meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .auth-pill {
            padding: 0.4rem 0.75rem;
            border-radius: 999px;
            background: rgba(148, 163, 184, 0.25);
            color: #e2e8f0;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .auth-form {
            padding: 2.6rem;
            background: #e4e3e3;
        }

        .auth-back {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.85rem;
            font-weight: 600;
            color: #1d4ed8;
            text-decoration: none;
        }

        .auth-back:hover {
            color: #0f172a;
        }

        .auth-form__header h2 {
            font-size: 1.55rem;
            margin-bottom: 0.35rem;
            font-weight: 700;
        }

        .auth-form__header p {
            color: #64748b;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
        }

        .auth-field {
            margin-bottom: 1rem;
        }

        .auth-label {
            display: block;
            font-weight: 600;
            font-size: 0.85rem;
            color: #475569;
            margin-bottom: 0.35rem;
        }

        .auth-input {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 0.75rem 0.9rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .auth-input i {
            color: #94a3b8;
        }

        .auth-input input {
            border: none;
            outline: none;
            background: transparent;
            width: 100%;
            font-size: 0.95rem;
            color: #0f172a;
        }

        .auth-input:focus-within {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
        }

        .auth-submit {
            width: 100%;
            border: none;
            border-radius: 999px;
            padding: 0.9rem 1rem;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff;
            font-weight: 600;
            font-size: 0.95rem;
            box-shadow: 0 10px 24px rgba(37, 99, 235, 0.3);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .auth-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 14px 30px rgba(37, 99, 235, 0.35);
        }

        .auth-back-btn {
            width: 100%;
            margin-top: 0.75rem;
            border: 1px solid #2563eb;
            border-radius: 999px;
            padding: 0.9rem 1rem;
            background: transparent;
            color: #1d4ed8;
            font-weight: 600;
            font-size: 0.95rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease, color 0.2s ease;
        }

        .auth-back-btn:hover {
            background: #1d4ed8;
            color: #ffffff;
            box-shadow: 0 10px 24px rgba(37, 99, 235, 0.25);
        }

        .auth-footer {
            margin-top: 1rem;
            text-align: center;
            font-size: 0.85rem;
            color: #64748b;
        }

        .auth-link {
            color: #1d4ed8;
            font-weight: 600;
            text-decoration: none;
        }

        .auth-link:hover {
            color: #0f172a;
        }

        @media (max-width: 900px) {
            .auth-card__inner {
                grid-template-columns: 1fr;
                min-height: auto;
            }

            .auth-panel {
                border-radius: 24px 24px 0 0;
                order: 1;
            }

            .auth-form {
                padding: 2rem;
                border-radius: 0 0 24px 24px;
                order: 2;
            }
        }

        @media (max-width: 520px) {
            .auth-shell {
                padding: 2rem 1rem 3rem;
            }

            .auth-panel,
            .auth-form {
                padding: 1.6rem;
            }

            .auth-panel h1 {
                font-size: 1.5rem;
            }
        }
    </style>
    <x-slot name="title">
        Register - UMKM Kuliner
    </x-slot>
    <div class="auth-register">
        <div class="auth-card">
            <div class="auth-card__inner">
                <section class="auth-panel">
                    <div class="auth-panel__badge">
                        <i class="fas fa-user-plus"></i>Akun Baru
                    </div>
                    <h1>Buat akun untuk mulai jelajahi UMKM favoritmu.</h1>
                    <p>Registrasi cepat, simpan preferensi, dan dapatkan rekomendasi yang relevan.</p>

                    <div class="auth-panel__list">
                        <div class="auth-panel__item">
                            <i class="fas fa-check-circle"></i>Daftar rekomendasi personal
                        </div>
                        <div class="auth-panel__item">
                            <i class="fas fa-check-circle"></i>Simpan preferensi kategori
                        </div>
                        <div class="auth-panel__item">
                            <i class="fas fa-check-circle"></i>Update promo terbaru
                        </div>
                    </div>

                    <div class="auth-panel__meta">
                        <span class="auth-pill">Mudah</span>
                        <span class="auth-pill">Cepat</span>
                        <span class="auth-pill">Gratis</span>
                    </div>
                </section>

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
                            <x-input-error :messages="$errors->get('name')" class="mt-1" />
                        </div>

                        <div class="auth-field">
                            <label for="email" class="auth-label">Email</label>
                            <div class="auth-input">
                                <i class="fas fa-envelope"></i>
                                <input id="email" type="email" name="email" value="{{ old('email') }}" required
                                    autocomplete="username" placeholder="contoh@email.com" />
                            </div>
                            <x-input-error :messages="$errors->get('email')" class="mt-1" />
                        </div>

                        <div class="auth-field">
                            <label for="password" class="auth-label">Password</label>
                            <div class="auth-input">
                                <i class="fas fa-lock"></i>
                                <input id="password" type="password" name="password" required
                                    autocomplete="new-password" placeholder="Minimal 8 karakter" />
                            </div>
                            <x-input-error :messages="$errors->get('password')" class="mt-1" />
                        </div>

                        <div class="auth-field">
                            <label for="password_confirmation" class="auth-label">Konfirmasi Password</label>
                            <div class="auth-input">
                                <i class="fas fa-lock"></i>
                                <input id="password_confirmation" type="password" name="password_confirmation" required
                                    autocomplete="new-password" placeholder="Ulangi password" />
                            </div>
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
                        </div>

                        <button class="auth-submit" type="submit">Buat Akun</button>

                        <a class="auth-back-btn" href="{{ url('/') }}">
                            <i class="fas fa-arrow-left"></i>Kembali
                        </a>

                        <div class="auth-footer">
                            Sudah punya akun? <a class="auth-link" href="{{ route('login') }}">Login</a>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </div>
</x-guest-layout>
