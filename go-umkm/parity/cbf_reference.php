<?php

/**
 * Sisi PHP dari uji paritas Sprint 2.
 *
 * Isi skrip ini SALINAN VERBATIM blok scoring di
 * app/Http/Controllers/DataUmkmController.php baris 98-172 (tanpa filter
 * request, tanpa pagination, tanpa view). Kalau controller berubah, salinan ini
 * harus ikut berubah — itulah risiko yang diukur oleh test paritas Go.
 *
 * Jalankan dari root project:
 *   php go-umkm/parity/cbf_reference.php go-umkm/parity/cbf_scenarios.json
 */

ini_set('display_errors', 'stderr');

require __DIR__ . '/../../vendor/autoload.php';

$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Kategori;
use App\Models\Umkm;
use App\Models\UserActivity;
use Illuminate\Support\Facades\DB;

$path = $argv[1] ?? 'go-umkm/parity/cbf_scenarios.json';
$scenarios = json_decode(file_get_contents($path), true);

if (!is_array($scenarios)) {
    fwrite(STDERR, "gagal parse {$path}\n");
    exit(1);
}

// ---------------------------------------------------------------------------
// Data dasar, sama persis dengan landing() tanpa filter request.
// ---------------------------------------------------------------------------
$allUmkms = Umkm::with(['lokasi', 'kategori.kelompok', 'rating'])
    ->orderBy('nama_umkm')->orderBy('id_umkm')->get();

$kategoriList = Kategori::with('kelompok')->orderBy('nama_kategori')->get();
$kategoriToKelompok = $kategoriList->pluck('id_kelompok', 'id_kategori')->toArray();
$kelompokGroups = $kategoriList->groupBy('id_kelompok');

$out = [];

foreach ($scenarios as $scenario) {
    $preferredCategoryIds = collect($scenario['explicit'] ?? [])
        ->map(fn($id) => (int) $id)->filter()->unique()->values()->all();

    $explicitScores = collect($preferredCategoryIds)
        ->mapWithKeys(fn($id) => [(int) $id => 1.0])->all();

    $userId = (int) ($scenario['user_id'] ?? 0) ?: null;
    $sessionId = $scenario['session_id'] ?? '';

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
    $finalCategoryScores = $baseCategoryScores;
    foreach ($baseCategoryScores as $catId => $score) {
        if ($score > 0) {
            $kelompokId = $kategoriToKelompok[$catId] ?? null;
            if ($kelompokId && isset($kelompokGroups[$kelompokId])) {
                foreach ($kelompokGroups[$kelompokId] as $siblingCat) {
                    $siblingId = (int) $siblingCat->id_kategori;
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

    // H. Alokasi Proporsional agar 3 kategori muncul semua (Format 4-3-3)
    $recommendedUmkms = collect();
    $allocationLimits = [4, 3, 3];

    foreach ($topCategoryIds as $index => $catId) {
        $limit = $allocationLimits[$index] ?? 3;

        $umkmsInCat = $scoredUmkms
            ->where('id_kategori', $catId)
            ->sortByDesc(fn($u) => $u->rating->avg('nilai_rating') ?? 0)
            ->take($limit);

        $recommendedUmkms = $recommendedUmkms->merge($umkmsInCat);
    }

    $out[] = [
        'nama' => $scenario['nama'] ?? '',
        'top_category_ids' => array_values($topCategoryIds),
        'skor_kategori' => collect($finalCategoryScores)->map(fn($v) => (float) $v)->all(),
        'recommended' => $recommendedUmkms->values()->map(function ($u) use ($topCategoryIds) {
            $index = array_search((int) $u->id_kategori, $topCategoryIds, true);
            return [
                'id_umkm' => (int) $u->id_umkm,
                'peringkat' => $index === false ? 0 : $index + 1,
            ];
        })->all(),
    ];
}

echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), "\n";
