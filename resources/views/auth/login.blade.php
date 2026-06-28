<x-guest-layout>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap');

        .auth-shell {
            background: radial-gradient(900px 520px at 15% 10%, rgba(236, 253, 245, 0.85) 0%, rgba(236, 253, 245, 0) 65%),
                radial-gradient(800px 420px at 95% 20%, rgba(219, 234, 254, 0.9) 0%, rgba(219, 234, 254, 0) 70%),
                #f8fafc !important;
            padding: 3rem 1.5rem 4rem;
        }

        .auth-logo {
            display: none;
        }

        .auth-card {
            max-width: 960px !important;
            width: min(960px, 100%);
            border-radius: 24px !important;
            border: 1px solid rgba(148, 163, 184, 0.3) !important;
            box-shadow: 0 28px 60px rgba(15, 23, 42, 0.15) !important;
            padding: 0 !important;
            overflow: hidden;
            background: #3ff532 !important;
        }

        .auth-login {
            font-family: 'Space Grotesk', sans-serif;
            color: #0f172a;
        }

        .auth-card__inner {
            display: grid;
            grid-template-columns: minmax(0, 1.1fr) minmax(0, 1fr);
            min-height: 520px;
        }

        .auth-panel {
            padding: 2.8rem;
            background: linear-gradient(135deg, #0f766e 0%, #0f172a 70%);
            color: #e2e8f0;
            position: relative;
            overflow: hidden;
        }

        .auth-panel::after {
            content: '';
            position: absolute;
            right: -120px;
            bottom: -120px;
            width: 260px;
            height: 260px;
            background: radial-gradient(circle, rgba(45, 212, 191, 0.45) 0%, rgba(45, 212, 191, 0) 70%);
        }

        .auth-panel__badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.35rem 0.75rem;
            border-radius: 999px;
            background: rgba(45, 212, 191, 0.18);
            color: #5eead4;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .auth-panel h1 {
            font-size: 2rem;
            line-height: 1.2;
            margin: 1rem 0 0.75rem;
            color: #39cc7b;
        }

        .auth-panel p {
            color: rgba(226, 232, 240, 0.8);
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
            color: #5eead4;
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
            color: #0f766e;
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
            border-color: #14b8a6;
            box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.2);
        }

        .auth-actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 0.75rem;
            align-items: center;
            margin: 0.75rem 0 1.5rem;
            font-size: 0.85rem;
        }

        .auth-check {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            color: #475569;
            font-weight: 600;
        }

        .auth-check input {
            accent-color: #14b8a6;
        }

        .auth-link {
            color: #0f766e;
            font-weight: 600;
            text-decoration: none;
        }

        .auth-link:hover {
            color: #0f172a;
        }

        .auth-submit {
            width: 100%;
            border: none;
            border-radius: 999px;
            padding: 0.9rem 1rem;
            background: linear-gradient(135deg, #14b8a6 0%, #0f766e 100%);
            color: #ffffff;
            font-weight: 600;
            font-size: 0.95rem;
            box-shadow: 0 10px 24px rgba(15, 118, 110, 0.3);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .auth-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 14px 30px rgba(15, 118, 110, 0.35);
        }

        .auth-back-btn {
            width: 100%;
            margin-top: 0.75rem;
            border: 1px solid #0f766e;
            border-radius: 999px;
            padding: 0.9rem 1rem;
            background: transparent;
            color: #0f766e;
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
            background: #0f766e;
            color: #ffffff;
            box-shadow: 0 10px 24px rgba(15, 118, 110, 0.25);
        }

        .auth-footer {
            margin-top: 1rem;
            text-align: center;
            font-size: 0.85rem;
            color: #64748b;
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
        Login - UMKM Kuliner
    </x-slot>
    <div class="auth-login">
        <div class="auth-card__inner">
            <section class="auth-panel">
                <div class="auth-panel__badge">
                    <i class="fas fa-utensils"></i>UMKM Kuliner
                </div>
                <h1>Masuk untuk rekomendasi kuliner yang lebih tepat.</h1>
                <p>Kelola preferensi, simpan rekomendasi, dan bantu kami mengenal rasa favoritmu.</p>

                <div class="auth-panel__list">
                    <div class="auth-panel__item">
                        <i class="fas fa-check-circle"></i>Rekomendasi UMKM personal
                    </div>
                    <div class="auth-panel__item">
                        <i class="fas fa-check-circle"></i>Riwayat preferensi tersimpan
                    </div>
                    <div class="auth-panel__item">
                        <i class="fas fa-check-circle"></i>Detail UMKM lebih cepat
                    </div>
                </div>

                <div class="auth-panel__meta">
                    <span class="auth-pill">Aman</span>
                    <span class="auth-pill">Responsif</span>
                    <span class="auth-pill">Modern</span>
                </div>
            </section>

            <section class="auth-form">

                <div class="auth-form__header">
                    <h2>Login Akun</h2>
                    <p>Masukkan email dan password yang terdaftar.</p>
                </div>

                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="auth-field">
                        <label for="email" class="auth-label">Email</label>
                        <div class="auth-input">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required
                                autofocus autocomplete="username" placeholder="contoh@email.com" />
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="mt-1" />
                    </div>

                    <div class="auth-field">
                        <label for="password" class="auth-label">Password</label>
                        <div class="auth-input">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password" id="password" required
                                autocomplete="current-password" placeholder="Masukkan password" />
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-1" />
                    </div>

                    <div class="auth-actions">
                        <label class="auth-check" for="remember_me">
                            <input id="remember_me" type="checkbox" name="remember">
                            Ingat saya
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="auth-link">Lupa password?</a>
                        @endif
                    </div>

                    <button class="auth-submit" type="submit">Masuk</button>

                    <a class="auth-back-btn" href="{{ url('/') }}">
                        <i class="fas fa-arrow-left"></i>Kembali
                    </a>

                    @if (Route::has('register'))
                        <div class="auth-footer">
                            Belum punya akun? <a class="auth-link" href="{{ route('register') }}">Daftar</a>
                        </div>
                    @endif
                </form>
            </section>
        </div>
    </div>
</x-guest-layout>
