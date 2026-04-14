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
    public function index()
    {
        $ratings = Rating::with('umkm')
            ->latest('created_at')
            ->paginate(20);

        return view('admin.rating.index', compact('ratings'));
    }

    /**
     * Show rating detail
     */
    public function show(Rating $rating)
    {
        $rating->load('umkm');

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
