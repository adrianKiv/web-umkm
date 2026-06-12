<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserActivity;
use Illuminate\Http\Request;

class UserActivityAdminController extends Controller
{
    /**
     * Display a listing of user activities.
     */
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $activities = UserActivity::with(['user', 'kategori'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->whereHas('user', function ($u) use ($q) {
                        $u->where('name', 'LIKE', "%{$q}%")
                          ->orWhere('email', 'LIKE', "%{$q}%");
                    })
                    ->orWhereHas('kategori', function ($k) use ($q) {
                        $k->where('nama_kategori', 'LIKE', "%{$q}%");
                    })
                    ->orWhere('interaction_type', 'LIKE', "%{$q}%")
                    ->orWhere('id_session', 'LIKE', "%{$q}%");
                });
            })
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.activities.index', compact('activities', 'q'));
    }
}
