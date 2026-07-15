<?php

namespace App\Http\Controllers;

use App\Models\Umkm;
use App\Models\lokasi;
use App\Models\Kategori;
use App\Models\Kelompok;
use App\Models\Rating;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
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
    // =========================================================================
    // 1. QUERY BUILDER & PENCARIAN (Filter Data Utama)
    // =========================================================================
    $query = Umkm::with(['lokasi', 'kategori.kelompok', 'rating']);

    // Filter berdasarkan Kategori spesifik
    if ($request->filled('id_kategori')) {
        $query->where('id_kategori', $request->id_kategori);
    }

    // Filter berdasarkan Kelompok (melalui relasi kategori)
    if ($request->filled('id_kelompok')) {
        $idKelompok = $request->id_kelompok;
        $query->whereHas('kategori', function($kq) use ($idKelompok) {
            $kq->where('id_kelompok', $idKelompok);
        });
    }

    // Filter berdasarkan rata-rata rating minimum
    if ($request->filled('min_rating') && (float) $request->min_rating > 0) {
        $minRating = (float) $request->min_rating;
        $query->whereRaw(
            '(select coalesce(avg(r.nilai_rating), 0) from rating r where r.id_umkm = umkm.id_umkm) >= ?',
            [$minRating]
        );
    }

    // Pencarian teks (Nama UMKM, Alamat, atau Nama Kategori)
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

    // Eksekusi query untuk mendapatkan semua data dasar yang sudah terfilter
    $allUmkms = (clone $query)->orderBy('nama_umkm')->orderBy('id_umkm')->get();


    // =========================================================================
    // 2. ALGORITMA CONTENT-BASED FILTERING (CBF) - Rekomendasi Personal
    // =========================================================================

    // A. Persiapkan Data Relasi Kategori & Kelompok (Untuk sebaran skor)
    $kategoriList = Kategori::with('kelompok')->orderBy('nama_kategori')->get();
    $kategoriToKelompok = $kategoriList->pluck('id_kelompok', 'id_kategori')->toArray();
    $kelompokGroups = $kategoriList->groupBy('id_kelompok');

    // B. Ambil Preferensi Eksplisit (Kuesioner/Modal awal user)
    $preferredCategoryIds = collect($request->session()->get('umkm_preferred_categories', []))
        ->map(fn($id) => (int) $id)->filter()->unique()->values()->all();

    $explicitScores = collect($preferredCategoryIds)
        ->mapWithKeys(fn($id) => [(int) $id => 1.0])->all();

    // C. Ambil Preferensi Implisit (Riwayat klik)
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

    // D. Kalkulasi Skor Dasar (Eksplisit + Implisit)
    $baseCategoryScores = $explicitScores;
    foreach ($implicitRows as $row) {
        $catId = (int) $row->id_kategori;
        $baseCategoryScores[$catId] = ($baseCategoryScores[$catId] ?? 0) + ((int) $row->total * 0.1);
    }

    // E. DISTRIBUSI SKOR KELOMPOK
    // Jika user suka Kategori A, beri juga sedikit bobot ke Kategori B & C yang sekelompok
    $finalCategoryScores = $baseCategoryScores;
    foreach ($baseCategoryScores as $catId => $score) {
        if ($score > 0) {
            $kelompokId = $kategoriToKelompok[$catId] ?? null;
            if ($kelompokId && isset($kelompokGroups[$kelompokId])) {
                foreach ($kelompokGroups[$kelompokId] as $siblingCat) {
                    $siblingId = (int) $siblingCat->id_kategori;
                    // Beri cipratan 50% skor ke kategori saudara, biarkan kategori asli tetap
                    if ($siblingId !== $catId) {
                        $finalCategoryScores[$siblingId] = ($finalCategoryScores[$siblingId] ?? 0) + ($score * 0.5);
                    }
                }
            }
        }
    }

    // F. Terapkan Skor ke Masing-masing UMKM
    $scoredUmkms = $allUmkms->map(function ($umkm) use ($finalCategoryScores) {
        $score = (float) ($finalCategoryScores[(int) $umkm->id_kategori] ?? 0);
        $umkm->setAttribute('recommendation_score', $score);
        return $umkm;
    });

    // G. Tentukan 3 Kategori Teratas
    $topCategoryIds = collect($finalCategoryScores)
        ->mapWithKeys(fn($v, $k) => [(int) $k => (float) $v])
        ->sortDesc()->keys()->take(3)->map(fn($id) => (int) $id)->values()->all();

    // Tandai UMKM mana saja yang masuk dalam kategori rekomendasi (Untuk Badge)
    $allUmkms->each(function ($umkm) use ($topCategoryIds) {
        $umkm->setAttribute('is_recommended', in_array((int) $umkm->id_kategori, $topCategoryIds, true));
    });

    // H. Alokasi Proporsional agar 3 kategori muncul semua (Format 4-3-3)
    $recommendedUmkms = collect();
    $allocationLimits = [4, 3, 3]; // Peringkat 1 (Max 4), Peringkat 2 (Max 3), Peringkat 3 (Max 3)

    foreach ($topCategoryIds as $index => $catId) {
        $limit = $allocationLimits[$index] ?? 3;

        $umkmsInCat = $scoredUmkms
            ->where('id_kategori', $catId)
            // Lakukan sort sekunder berdasarkan rating tertinggi agar yang tampil adalah yang terbaik di kategori tersebut
            ->sortByDesc(fn($u) => $u->rating->avg('nilai_rating') ?? 0)
            ->take($limit);

        $recommendedUmkms = $recommendedUmkms->merge($umkmsInCat);
    }

    // =========================================================================
    // 3. PAGINATION (Pemotongan Data untuk Halaman Utama)
    // =========================================================================
    $page = LengthAwarePaginator::resolveCurrentPage();
    $perPage = 12;
    $currentItems = $allUmkms->slice(($page - 1) * $perPage, $perPage)->values();

    $umkms = new LengthAwarePaginator(
        $currentItems,
        $allUmkms->count(),
        $perPage,
        $page,
        ['path' => secure_url($request->path()), 'query' => $request->query()]
    );
    $umkms->appends($request->query());


    // =========================================================================
    // 4. LEADERBOARD (Top 10 UMKM Terpopuler Secara Global)
    // =========================================================================
    $topClicks = Umkm::with(['kategori', 'rating'])
        ->select('id_umkm', 'nama_umkm', 'total_klik', 'id_kategori', 'foto_umkm')
        ->orderByDesc('total_klik')
        ->take(10)
        ->get();

    $topClicksLabels = $topClicks->map(fn($item) => $item->nama_umkm)->values();
    $topClicksValues = $topClicks->map(fn($item) => (int) ($item->total_klik ?? 0))->values();


    // =========================================================================
    // 5. DATA MASTER & RENDER VIEW (Kebutuhan UI/UX)
    // =========================================================================
    $kelompokList = Kelompok::orderBy('nama_kelompok')->get();
    $kategoriList = Kategori::with('kelompok')->orderBy('nama_kategori')->get();

    // Logika pop-up pemilihan preferensi pengguna baru
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
        'topClicks',
        'topClicksLabels',
        'topClicksValues',
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
        $dataUmkms = Umkm::select('id_umkm', 'nama_umkm')->orderBy('nama_umkm')->get();
        $kategoriList = Kategori::orderBy('nama_kategori')->get();

        $preferredCategoryIds = collect($request->session()->get('umkm_preferred_categories', []))
            ->map(fn($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        // Check if specific UMKM is requested
        $selectedUmkm = null;
        $selectedId = $request->input('umkm') ?: $request->input('umkm_id');
        if ($selectedId) {
            $selectedUmkm = Umkm::with(['lokasi', 'kategori.kelompok', 'rating', 'menu'])
                               ->find($selectedId);
        }

        return view('map', compact('dataUmkms', 'selectedUmkm', 'kategoriList'));
    }

    /**
     * Return UMKM that fall inside the current map bounding box.
     */
    public function mapData(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'north' => ['required', 'numeric'],
            'south' => ['required', 'numeric'],
            'east' => ['required', 'numeric'],
            'west' => ['required', 'numeric'],
        ]);

        $highlightCategoryIds = $this->getHighlightCategoryIds($request);

        $umkms = Umkm::with(['lokasi', 'kategori.kelompok', 'rating', 'menu'])
            ->whereHas('lokasi', function ($query) use ($validated) {
                $query->whereBetween('latitude', [(float) $validated['south'], (float) $validated['north']])
                    ->whereBetween('longitude', [(float) $validated['west'], (float) $validated['east']]);
            })
            ->get()
            ->map(fn ($item) => $this->buildMapPayload($item, $highlightCategoryIds))
            ->values();

        return response()->json([
            'data' => $umkms,
            'count' => $umkms->count(),
        ]);
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
            'avg_rating' => (float) ($umkm->rating->avg('nilai_rating') ?? 0),
            'rating_count' => (int) $umkm->rating->count(),
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

    private function getHighlightCategoryIds(Request $request): array
    {
        $preferredCategoryIds = collect($request->session()->get('umkm_preferred_categories', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $explicitScores = collect($preferredCategoryIds)
            ->mapWithKeys(fn ($id) => [(int) $id => 1.0])
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

        return collect($categoryScores)
            ->mapWithKeys(fn ($v, $k) => [(int) $k => (float) $v])
            ->sortDesc()
            ->keys()
            ->take(3)
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    private function buildMapPayload(Umkm $item, array $highlightCategoryIds): array
    {
        return [
            'id' => $item->id_umkm,
            'nama_umkm' => $item->nama_umkm,
            'foto_umkm_url' => $item->foto_umkm_url,
            'no_telfon' => $item->no_telfon,
            'kategori' => optional($item->kategori)->nama_kategori ?? 'Tidak dikategorikan',
            'kelompok' => optional(optional($item->kategori)->kelompok)->nama_kelompok ?? 'Tanpa Kelompok',
            'jam_buka' => $item->jam_buka,
            'alamat_lengkap' => $item->alamat_lengkap,
            'deskripsi' => $item->deskripsi,
            'latitude' => (float) optional($item->lokasi)->latitude,
            'longitude' => (float) optional($item->lokasi)->longitude,
            'rating_avg' => (float) ($item->rating->avg('nilai_rating') ?? 0),
            'rating_count' => (int) $item->rating->count(),
            'ulasan' => $item->rating
                ->sortByDesc('created_at')
                ->map(fn ($rating) => [
                    'nama_pengulas' => $rating->nama_pengulas ?: 'Anonymous',
                    'nilai_rating' => (int) $rating->nilai_rating,
                    'komentar' => $rating->komentar,
                    'tanggal' => optional($rating->created_at)->format('Y-m-d H:i:s'),
                ])
                ->values(),
            'menu' => $item->menu
                ->map(fn ($menu) => [
                    'id' => $menu->id_menu,
                    'nama_menu' => $menu->nama_menu,
                    'harga_menu' => (float) $menu->harga_menu,
                    'foto_menu_url' => $menu->foto_menu_url,
                    'is_daftar_foto' => (bool) $menu->is_foto_daftar_menu,
                ])
                ->values(),
            'is_recommended' => in_array((int) $item->id_kategori, $highlightCategoryIds, true),
        ];
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
