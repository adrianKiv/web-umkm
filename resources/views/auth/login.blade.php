<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <style>
        /* From Uiverse.io by bociKond */
        .form {
          --bg-light: #efefef;
          --bg-dark: #707070;
          --clr: #58bc82;
          --clr-alpha: #9c9c9c60;
          display: flex;
          flex-direction: column;
          align-items: center;
          gap: 1rem;
          width: 100%;
          max-width: 300px;
          margin: 0 auto; /* Tambahan agar form di tengah */
        }

        .form .input-span {
          width: 100%;
          display: flex;
          flex-direction: column;
          gap: 0.5rem;
        }

        .form input[type="email"],
        .form input[type="password"] {
          border-radius: 0.5rem;
          padding: 1rem 0.75rem;
          width: 100%;
          border: none;
          display: flex;
          align-items: center;
          gap: 0.5rem;
          background-color: var(--clr-alpha);
          outline: 2px solid var(--bg-dark);
          color: #333; /* Tambahan warna teks agar terbaca */
        }

        .form input[type="email"]:focus,
        .form input[type="password"]:focus {
          outline: 2px solid var(--clr);
        }

        .label {
          align-self: flex-start;
          color: var(--clr);
          font-weight: 600;
        }

        .form .submit {
          padding: 1rem 0.75rem;
          width: 100%;
          display: flex;
          align-items: center;
          justify-content: center;
          gap: 0.5rem;
          border-radius: 3rem;
          background-color: var(--bg-dark);
          color: var(--bg-light);
          border: none;
          cursor: pointer;
          transition: all 300ms;
          font-weight: 600;
          font-size: 0.9rem;
        }

        .form .submit:hover {
          background-color: var(--clr);
          color: var(--bg-dark);
        }

        .span {
          text-decoration: none;
          color: var(--bg-dark);
          font-size: 0.9rem;
        }

        .span a {
          color: var(--clr);
        }

        /* Styling tambahan untuk checkbox Remember Me */
        .remember-span {
          display: flex;
          align-items: center;
          gap: 0.5rem;
          align-self: flex-start;
        }
    </style>

    <form method="POST" action="{{ route('login') }}" class="form">
        @csrf

        <span class="input-span">
            <label for="email" class="label">Email</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </span>

        <span class="input-span">
            <label for="password" class="label">Password</label>
            <input type="password" name="password" id="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </span>

        <span class="remember-span span">
            <input id="remember_me" type="checkbox" name="remember" style="accent-color: var(--clr);">
            <label for="remember_me">{{ __('Remember me') }}</label>
        </span>

        @if (Route::has('password.request'))
            <span class="span">
                <a href="{{ route('password.request') }}">Forgot password?</a>
            </span>
        @endif

        <input class="submit" type="submit" value="Log in" />

        @if (Route::has('register'))
            <span class="span">Don't have an account? <a href="{{ route('register') }}">Sign up</a></span>
        @endif
    </form>
</x-guest-layout>
