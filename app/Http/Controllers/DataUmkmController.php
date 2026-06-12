<?php

namespace App\Http\Controllers;

use App\Models\Umkm;
use App\Models\lokasi;
use App\Models\Kategori;
use App\Models\Kelompok;
use App\Models\Rating;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        // Filter options
        $kelompokList = Kelompok::orderBy('nama_kelompok')->get();
        $kategoriList = Kategori::with('kelompok')->orderBy('nama_kategori')->get();

        $allUmkms = $filteredQuery->get();
        $preferredCategoryIds = collect($request->session()->get('umkm_preferred_categories', []))
            ->map(fn($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $explicitScores = collect($preferredCategoryIds)
            ->mapWithKeys(fn($id) => [(int) $id => 1.0])
            ->all();

        $userId = Auth::id();
        $sessionId = $request->session()->getId();

        $implicitRows = UserActivity::query()
            ->select('id_kategori', DB::raw('COUNT(*) as total'))
            ->where('interaction_type', 'detail_click')
            ->when($userId, function ($query) use ($userId) {
                $query->where('id_user', $userId);
            }, function ($query) use ($sessionId) {
                $query->where('id_session', $sessionId);
            })
            ->groupBy('id_kategori')
            ->get();

        $categoryScores = $explicitScores;
        foreach ($implicitRows as $row) {
            $categoryId = (int) $row->id_kategori;
            $categoryScores[$categoryId] = ($categoryScores[$categoryId] ?? 0) + ((int) $row->total * 0.1);
        }

        $scoredUmkms = $allUmkms->map(function ($umkm) use ($categoryScores) {
            $score = (float) ($categoryScores[(int) $umkm->id_kategori] ?? 0);
            $umkm->setAttribute('recommendation_score', $score);
            return $umkm;
        });

        // Determine top 3 categories by score to be used for recommendations
        $topCategoryIds = collect($categoryScores)
            ->mapWithKeys(fn($v, $k) => [(int) $k => (float) $v])
            ->sortDesc()
            ->keys()
            ->take(3)
            ->map(fn($id) => (int) $id)
            ->values()
            ->all();

        // Recommend UMKMs only from the top categories
        $recommendedUmkms = $scoredUmkms
            ->filter(fn($umkm) => in_array((int) $umkm->id_kategori, $topCategoryIds, true))
            ->sortByDesc(fn($umkm) => $umkm->recommendation_score)
            ->take(6);

        $allUmkms->each(function ($umkm) use ($topCategoryIds) {
            $umkm->setAttribute('is_recommended', in_array((int) $umkm->id_kategori, $topCategoryIds, true));
        });

        $randomizedUmkms = $allUmkms->shuffle();
        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 12;
        $currentItems = $randomizedUmkms->slice(($page - 1) * $perPage, $perPage)->values();
        $umkms = new LengthAwarePaginator(
            $currentItems,
            $randomizedUmkms->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()],
        );

        $shouldShowPreferenceModal = empty($preferredCategoryIds)
            && !$request->session()->has('umkm_preference_prompted');

        if ($shouldShowPreferenceModal) {
            $request->session()->put('umkm_preference_prompted', true);
        }

        return view('landing', compact(
            'umkms',
            'recommendedUmkms',
            'kelompokList',
            'kategoriList',
            'preferredCategoryIds',
            'shouldShowPreferenceModal'
        ));
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

        $preferredCategoryIds = collect($request->session()->get('umkm_preferred_categories', []))
            ->map(fn($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $explicitScores = collect($preferredCategoryIds)
            ->mapWithKeys(fn($id) => [(int) $id => 1.0])
            ->all();

        $userId = Auth::id();
        $sessionId = $request->session()->getId();

        $implicitRows = UserActivity::query()
            ->select('id_kategori', DB::raw('COUNT(*) as total'))
            ->where('interaction_type', 'detail_click')
            ->when($userId, function ($query) use ($userId) {
                $query->where('id_user', $userId);
            }, function ($query) use ($sessionId) {
                $query->where('id_session', $sessionId);
            })
            ->groupBy('id_kategori')
            ->get();

        $categoryScores = $explicitScores;
        foreach ($implicitRows as $row) {
            $categoryId = (int) $row->id_kategori;
            $categoryScores[$categoryId] = ($categoryScores[$categoryId] ?? 0) + ((int) $row->total * 0.1);
        }

        // Use top 3 categories as highlights on the map as well
        $highlightCategoryIds = collect($categoryScores)
            ->mapWithKeys(fn($v, $k) => [(int) $k => (float) $v])
            ->sortDesc()
            ->keys()
            ->take(3)
            ->map(fn($id) => (int) $id)
            ->values()
            ->all();

        // Check if specific UMKM is requested
        $selectedUmkm = null;
        $selectedId = $request->input('umkm') ?: $request->input('umkm_id');
        if ($selectedId) {
            $selectedUmkm = Umkm::with(['lokasi', 'kategori.kelompok', 'rating', 'menu'])
                               ->find($selectedId);
        }

        return view('map', compact('dataUmkms', 'selectedUmkm', 'kategoriList', 'highlightCategoryIds'));
    }

    /**
     * Return UMKM detail for the landing page modal.
     */
    public function detail(Umkm $umkm)
    {
        Umkm::whereKey($umkm->getKey())
            ->update(['total_klik' => DB::raw('COALESCE(total_klik, 0) + 1')]);

        $umkm->load('kategori.kelompok');

        return response()->json([
            'id' => $umkm->id_umkm,
            'nama_umkm' => $umkm->nama_umkm,
            'foto_umkm_url' => $umkm->foto_umkm_url,
            'jam_buka' => $umkm->jam_buka,
            'alamat_lengkap' => $umkm->alamat_lengkap,
            'no_telfon' => $umkm->no_telfon,
            'kategori' => optional($umkm->kategori)->nama_kategori ?? 'Tidak dikategorikan',
        ]);
    }

    /**
     * Track implicit feedback for detail clicks.
     */
    public function trackActivity(Request $request, Umkm $umkm)
    {
        UserActivity::create([
            'id_user' => Auth::id(),
            'id_session' => $request->session()->getId(),
            'id_kategori' => $umkm->id_kategori,
            'interaction_type' => 'detail_click',
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Store user preference for content-based filtering.
     */
    public function storePreference(Request $request)
    {
        $validated = $request->validate([
            'kategori_ids' => 'required|array|max:3',
            'kategori_ids.*' => 'integer|exists:kategori,id_kategori',
        ]);

        $categoryIds = collect($validated['kategori_ids'])
            ->map(fn($id) => (int) $id)
            ->unique()
            ->take(3)
            ->values()
            ->all();

        if (count($categoryIds) === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Pilih minimal satu kategori.',
            ], 422);
        }

        $request->session()->put('umkm_preferred_categories', $categoryIds);

        return response()->json([
            'success' => true,
            'message' => 'Preferensi tersimpan.',
            'categories' => $categoryIds,
        ]);
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
