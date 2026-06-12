<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use Illuminate\Http\Request;

class RatingAdminController extends Controller
{
    /**
     * Display a listing of ratings
     */
    public function index(Request $request)
    {
        $ratings = Rating::with(['umkm.kategori'])
            ->latest('created_at')
            ->paginate(20);

            $search = trim((string) $request->query('search', ''));

            $ratings = Rating::with(['umkm.kategori'])
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($sub) use ($search) {
                        $sub->where('nama_pengulas', 'LIKE', "%{$search}%")
                            ->orWhere('komentar', 'LIKE', "%{$search}%")
                            ->orWhere('nilai_rating', 'LIKE', "%{$search}%")
                            ->orWhereHas('umkm', function ($u) use ($search) {
                                $u->where('nama_umkm', 'LIKE', "%{$search}%");
                            });
                    });
                })
                ->latest('created_at')
                ->paginate(20)
                ->withQueryString();

            return view('admin.rating.index', compact('ratings', 'search'));
    }

    /**
     * Show rating detail
     */
    public function show(Rating $rating)
    {
        $rating->load('umkm.kategori');

        return view('admin.rating.show', compact('rating'));
    }

    /**
     * Delete rating
     */
    public function destroy(Rating $rating)
    {
        $rating->delete();

        return redirect()->route('admin.rating.index')
            ->with('success', 'Rating berhasil dihapus');
    }
}
