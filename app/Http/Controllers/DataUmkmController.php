<?php

namespace App\Http\Controllers;

use App\Models\Umkm;
use App\Models\lokasi;
use App\Models\Kategori;
use App\Models\Kelompok;
use App\Models\Rating;
use Illuminate\Http\Request;

class DataUmkmController extends Controller
{
    /**
     * Landing page with search and grid display
     */
    public function landing(Request $request)
    {
        $query = Umkm::with(['lokasi', 'kategori.kelompok', 'rating']);

        // Filter by category
        if ($request->filled('id_kategori')) {
            $query->where('id_kategori', $request->id_kategori);
        }

        // Filter by group through category relation
        if ($request->filled('id_kelompok')) {
            $idKelompok = $request->id_kelompok;
            $query->whereHas('kategori', function($kq) use ($idKelompok) {
                $kq->where('id_kelompok', $idKelompok);
            });
        }

        // Filter by minimum average rating
        if ($request->filled('min_rating') && (float) $request->min_rating > 0) {
            $minRating = (float) $request->min_rating;
            $query->whereRaw(
                '(select coalesce(avg(r.nilai_rating), 0) from rating r where r.id_umkm = umkm.id_umkm) >= ?',
                [$minRating],
            );
        }

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_umkm', 'LIKE', "%{$search}%")
                  ->orWhere('alamat_lengkap', 'LIKE', "%{$search}%")
                  ->orWhereHas('kategori', function($kq) use ($search) {
                      $kq->where('nama_kategori', 'LIKE', "%{$search}%");
                  });
            });
        }

        $filteredQuery = clone $query;
        $umkms = $query->paginate(12);

        // Filter options
        $kelompokList = Kelompok::orderBy('nama_kelompok')->get();
        $kategoriList = Kategori::with('kelompok')->orderBy('nama_kategori')->get();

        // Get recommended UMKM from currently filtered result (top rated with at least 3 ratings)
        $allUmkms = $filteredQuery->get();

        $recommendedUmkms = $allUmkms->filter(function($umkm) {
            return $umkm->rating->count() >= 3;
        })->sortByDesc(function($umkm) {
            return $umkm->rating->avg('nilai_rating');
        })->take(6);

        return view('landing', compact('umkms', 'recommendedUmkms', 'kelompokList', 'kategoriList'));
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $dataUmkms = Umkm::with('lokasi')->paginate(15);
        return response()->json($dataUmkms);
    }

    /**
     * Line map view for UMKM data.
     */
    public function map(Request $request)
    {
        $dataUmkms = Umkm::with(['lokasi', 'kategori.kelompok', 'rating', 'menu'])->get();
        $kategoriList = Kategori::orderBy('nama_kategori')->get();

        // Check if specific UMKM is requested
        $selectedUmkm = null;
        if ($request->has('umkm') && $request->umkm) {
            $selectedUmkm = Umkm::with(['lokasi', 'kategori.kelompok', 'rating', 'menu'])
                               ->find($request->umkm);
        }

        return view('map', compact('dataUmkms', 'selectedUmkm', 'kategoriList'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $lokasis = lokasi::all();
        return response()->json(["lokasi" => $lokasis]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_usaha' => 'required|string|max:255',
            'id_lokasi' => 'required|exists:lokasi,id_lokasi',
        ]);

        $dataUmkm = Umkm::create($validated);
        return response()->json($dataUmkm, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Umkm $data_umkm)
    {
        $data_umkm->load('lokasi');
        return response()->json($data_umkm);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Umkm $data_umkm)
    {
        $lokasis = lokasi::all();
        $data_umkm->load('lokasi');
        return response()->json(["data_umkm" => $data_umkm, "lokasi" => $lokasis]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Umkm $data_umkm)
    {
        $validated = $request->validate([
            'nama_usaha' => 'required|string|max:255',
            'id_lokasi' => 'required|exists:lokasi,id_lokasi',
        ]);

        $data_umkm->update($validated);
        $data_umkm->load('lokasi');
        return response()->json($data_umkm);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Umkm $data_umkm)
    {
        $data_umkm->delete();
        return response()->json(null, 204);
    }

    /**
     * Store a new rating for UMKM
     */
    public function storeRating(Request $request)
    {
        // 1. Ubah 'required' menjadi 'nullable' agar validasi tidak gagal jika nama dikosongkan
        $validated = $request->validate([
            'nama_pengulas' => 'nullable|string|max:255',
            'nilai_rating' => 'required|integer|min:1|max:5',
            'komentar' => 'nullable|string|max:1000',
            'id_umkm' => 'required|exists:umkm,id_umkm',
        ]);

        // 2. Berikan kondisi: jika nama_pengulas kosong, ganti dengan "Anonymous"
        // Kita menggunakan $request->filled() untuk mengecek apakah ada input yang tidak kosong
        if (!$request->filled('nama_pengulas')) {
            $validated['nama_pengulas'] = 'Anonymous';
        }

        $rating = Rating::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Rating berhasil ditambahkan!',
            'rating' => $rating
        ]);
    }
}
