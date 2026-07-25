<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserActivity;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cookie;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
    $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
    // Ini adalah pesan custom-nya
    'email.required'    => 'Waduh, kolom email tidak boleh kosong ya!',
    'email.email'       => 'Pastikan format email yang dimasukkan sudah benar.',
    "email.unique"      => 'Email ini sudah terdaftar. Silakan gunakan email lain.',
    'password.required' => 'Password wajib diisi untuk masuk.',
    "password.confirmed" => 'Password konfirmasi tidak cocok dengan password yang dimasukkan.',
    'password.min'      => 'Password minimal harus 8 karakter.',
    ]);

    // 1. TANGKAP SESSION LAMA
    $oldSessionId = $request->session()->getId();
    $guestPreferredCategories = $request->session()->get('umkm_preferred_categories', []);

    // Pembuatan User baru bawaan Breeze
    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'id_role' => 1,
    ]);

    event(new Registered($user));

    Auth::login($user);

    // 2. MIGRASI DATA LOG KE AKUN BARU
    UserActivity::where('id_session', $oldSessionId)
        ->whereNull('id_user')
        ->update([
            'id_user' => $user->id
        ]);

    // 3. KEMBALIKAN DATA PREFERENSI
    if (!empty($guestPreferredCategories)) {
        $request->session()->put('umkm_preferred_categories', $guestPreferredCategories);
        Cookie::queue('umkm_preference_prompted', 'yes', 10080); // 7 hari
    }

        return redirect(route('dashboard', absolute: false));
    }
}
