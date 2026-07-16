<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\UserActivity;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // 1. TANGKAP SESSION LAMA & DATA PREFERENSI SEBELUM LOGIN
        $oldSessionId = $request->session()->getId();
        $guestPreferredCategories = $request->session()->get('umkm_preferred_categories', []);

        // Proses autentikasi bawaan Breeze
        $request->authenticate();

        // Laravel mereset Session ID di sini
        $request->session()->regenerate();

        // 2. MIGRASI DATA LOG (Implisit)
        $userId = Auth::id();
        UserActivity::where('id_session', $oldSessionId)
            ->whereNull('id_user')
            ->update([
                'id_user' => $userId
            ]);

        // 3. KEMBALIKAN DATA PREFERENSI EKSPLISIT KE SESSION BARU
        if (!empty($guestPreferredCategories)) {
            $request->session()->put('umkm_preferred_categories', $guestPreferredCategories);
            $request->session()->put('umkm_preference_prompted', true);
        }

        $user = $request->user();
        if ($user && ($user->isAdmin() || $user->isSuperAdmin())) {
            return redirect()->intended(route('admin.dashboard'));
        }

        return redirect()->route('landing');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
