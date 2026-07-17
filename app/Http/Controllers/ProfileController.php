<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\UserActivity;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user()->load('role');

        $activityCount = UserActivity::query()
            ->where('id_user', $user->id)
            ->count();

        $favoriteCategories = UserActivity::query()
            ->select('id_kategori', DB::raw('COUNT(*) as total'))
            ->where('id_user', $user->id)
            ->groupBy('id_kategori')
            ->orderByDesc('total')
            ->limit(3)
            ->get()
            ->map(function ($row) {
                $kategori = Kategori::with('kelompok')->find($row->id_kategori);

                return [
                    'nama_kategori' => $kategori?->nama_kategori ?? 'Tidak diketahui',
                    'nama_kelompok' => $kategori?->kelompok?->nama_kelompok ?? 'Tanpa kelompok',
                    'total' => (int) $row->total,
                ];
            });

        $recentActivities = $user->activities()
            ->with(['kategori.kelompok'])
            ->latest('created_at')
            ->limit(5)
            ->get();

        return view('profile.edit', [
            'user' => $user,
            'activityCount' => $activityCount,
            'favoriteCategories' => $favoriteCategories,
            'recentActivities' => $recentActivities,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
