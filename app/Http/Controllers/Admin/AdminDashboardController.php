<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Models\Lokasi;
use App\Models\Menu;
use App\Models\MenuSubmission;
use App\Models\Umkm;
use App\Models\Rating;
use App\Models\UmkmSubmission;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminDashboardController extends Controller
{
    /**
     * Show admin dashboard
     */
    public function index()
    {
        $stats = [
            'total_umkm' => Umkm::count(),
            'total_rating' => Rating::count(),
            'avg_rating' => Rating::avg('nilai_rating') ?? 0,
            'total_users' => User::count(),
            'total_menu' => menu::count(),
        ];

        $topClicks = Umkm::select('id_umkm', 'nama_umkm', 'total_klik')
            ->orderByDesc('total_klik')
            ->take(10)
            ->get();

        $lowestClicks = Umkm::select('id_umkm', 'nama_umkm', 'total_klik')
            ->orderBy('total_klik')
            ->take(10)
            ->get();

        $topClicksLabels = $topClicks->map(fn($item) => $item->nama_umkm)->values();
        $topClicksValues = $topClicks->map(fn($item) => (int) ($item->total_klik ?? 0))->values();

        $lowestClicksLabels = $lowestClicks->map(fn($item) => $item->nama_umkm)->values();
        $lowestClicksValues = $lowestClicks->map(fn($item) => (int) ($item->total_klik ?? 0))->values();

        $ratingByCategory = DB::table('kategori')
            ->leftJoin('umkm', 'kategori.id_kategori', '=', 'umkm.id_kategori')
            ->leftJoin('rating', 'rating.id_umkm', '=', 'umkm.id_umkm')
            ->select(
                'kategori.nama_kategori',
                DB::raw('ROUND(COALESCE(AVG(rating.nilai_rating), 0), 2) as avg_rating'),
            )
            ->groupBy('kategori.id_kategori', 'kategori.nama_kategori')
            ->orderByDesc('avg_rating')
            ->get();

        $ratingCategoryLabels = $ratingByCategory->pluck('nama_kategori');
        $ratingCategoryValues = $ratingByCategory->pluck('avg_rating');

        $recentUmkm = Umkm::with('kategori')
            ->latest('created_at')
            ->take(5)
            ->get();

        $recentRatings = Rating::with('umkm')
            ->latest('created_at')
            ->take(5)
            ->get();

        $kategoriStats = Kategori::withCount('umkm')
            ->orderBy('umkm_count', 'desc') // Mengurutkan dari yang paling banyak
            ->get();

        $wilayahKeywordMap = [
            'Isola' => ['isola'],
            'Gegerkalong' => ['gegerkalong'],
            'Ledeng' => ['ledeng'],
            'Sukasari' => ['sukasari'],
            'Cidadap' => ['cidadap'],
            'Setiabudi' => ['setiabudi', 'setia budi'],
            'Sarijadi' => ['sarijadi'],
            'Sukarasa' => ['sukarasa'],
            'Sukawarna' => ['sukawarna'],
            'Ciumbuleuit' => ['ciumbuleuit'],
        ];

        $wilayahCaseParts = [];
        foreach ($wilayahKeywordMap as $label => $keywords) {
            $keywordParts = [];
            foreach ($keywords as $keyword) {
                $normalized = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], strtolower($keyword));
                $keywordParts[] = "LOWER(alamat_lengkap) LIKE '%{$normalized}%'";
            }
            $wilayahCaseParts[] = 'WHEN ' . implode(' OR ', $keywordParts) . " THEN '{$label}'";
        }

        $wilayahCaseSql = 'CASE ' . implode(' ', $wilayahCaseParts) . " ELSE 'Lainnya' END";
        $wilayahRows = DB::table('umkm')
            ->select(DB::raw($wilayahCaseSql . ' as wilayah_label'), DB::raw('COUNT(*) as total'))
            ->groupBy('wilayah_label')
            ->orderByDesc('total')
            ->get();

        $wilayahLabels = $wilayahRows->pluck('wilayah_label');
        $wilayahValues = $wilayahRows->pluck('total');

        $jamBukaBuckets = [
            'Buka Pagi' => 0,
            'Buka Siang/Sore' => 0,
            'Buka Malam' => 0,
            'Buka 24 Jam' => 0,
        ];

        $jamBukaList = Umkm::select('jam_buka')->get();
        foreach ($jamBukaList as $umkm) {
            $jamBuka = strtolower(trim((string) $umkm->jam_buka));
            if ($jamBuka === '' || $jamBuka === '-') {
                continue;
            }

            if (Str::contains($jamBuka, ['24 jam', '24jam', '24-hour', '24 hours'])) {
                $jamBukaBuckets['Buka 24 Jam']++;
                continue;
            }

            if (Str::contains($jamBuka, ['00:00', '00.00']) && Str::contains($jamBuka, ['23:59', '23.59', '24:00', '24.00'])) {
                $jamBukaBuckets['Buka 24 Jam']++;
                continue;
            }

            if (preg_match('/(\d{1,2})[:.](\d{2})/', $jamBuka, $matches)) {
                $hour = (int) $matches[1];

                if ($hour >= 6 && $hour < 12) {
                    $jamBukaBuckets['Buka Pagi']++;
                } elseif ($hour >= 12 && $hour < 19) {
                    $jamBukaBuckets['Buka Siang/Sore']++;
                } else {
                    $jamBukaBuckets['Buka Malam']++;
                }
            }
        }

        $jamBukaLabels = collect(array_keys($jamBukaBuckets));
        $jamBukaValues = collect(array_values($jamBukaBuckets));

        // 1. Cek UMKM tanpa koordinat lokasi
        // Kondisi: id_lokasi kosong ATAU latitude/longitude di tabel lokasi kosong
        $umkmTanpaKoordinatQuery = Umkm::query()
            ->whereNull('id_lokasi')
            ->orWhereHas('lokasi', function ($query) {
                $query->whereNull('latitude')
                    ->orWhere('latitude', '')
                    ->orWhereNull('longitude')
                    ->orWhere('longitude', '');
            });
        $umkmTanpaKoordinat = (clone $umkmTanpaKoordinatQuery)->count();

        // 2. Cek UMKM tanpa nomor telepon
        $umkmTanpaTeleponQuery = Umkm::query()
            ->whereNull('no_telfon')
            ->orWhere('no_telfon', '-')
            ->orWhere('no_telfon', '');
        $umkmTanpaTelepon = (clone $umkmTanpaTeleponQuery)->count();

        $umkmTanpaJamQuery = Umkm::query()
            ->whereNull('jam_buka')
            ->orWhere('jam_buka', '-')
            ->orWhere('jam_buka', '');
        $umkmTanpaJam = (clone $umkmTanpaJamQuery)->count();

        $umkmTanpaAlamatQuery = Umkm::query()
            ->whereNull('alamat_lengkap')
            ->orWhere('alamat_lengkap', '-')
            ->orWhere('alamat_lengkap', '');
        $umkmTanpaAlamat = (clone $umkmTanpaAlamatQuery)->count();

        $umkmTanpaFotoQuery = Umkm::query()
            ->whereNull('foto_umkm')
            ->orWhere('foto_umkm', '-')
            ->orWhere('foto_umkm', '');
        $umkmTanpaFoto = (clone $umkmTanpaFotoQuery)->count();

        $umkmTanpaKoordinatIds = (clone $umkmTanpaKoordinatQuery)->pluck('id_umkm');
        $umkmTanpaTeleponIds = (clone $umkmTanpaTeleponQuery)->pluck('id_umkm');
        $umkmTanpaJamIds = (clone $umkmTanpaJamQuery)->pluck('id_umkm');
        $umkmTanpaAlamatIds = (clone $umkmTanpaAlamatQuery)->pluck('id_umkm');
        $umkmTanpaFotoIds = (clone $umkmTanpaFotoQuery)->pluck('id_umkm');

        $umkmPerluPerbaikanIds = $umkmTanpaKoordinatIds
            ->merge($umkmTanpaTeleponIds)
            ->merge($umkmTanpaJamIds)
            ->merge($umkmTanpaAlamatIds)
            ->merge($umkmTanpaFotoIds)
            ->unique()
            ->values();

        $umkmPerluPerbaikan = $umkmPerluPerbaikanIds->isEmpty()
            ? collect()
            : Umkm::with(['lokasi', 'kategori'])
                ->whereIn('id_umkm', $umkmPerluPerbaikanIds)
                ->orderByDesc('updated_at')
                ->get();

        $koordinatIdSet = array_fill_keys($umkmTanpaKoordinatIds->all(), true);
        $teleponIdSet = array_fill_keys($umkmTanpaTeleponIds->all(), true);
        $jamIdSet = array_fill_keys($umkmTanpaJamIds->all(), true);
        $alamatIdSet = array_fill_keys($umkmTanpaAlamatIds->all(), true);
        $fotoIdSet = array_fill_keys($umkmTanpaFotoIds->all(), true);

        $umkmPerluPerbaikan->each(function ($umkm) use ($koordinatIdSet, $teleponIdSet, $jamIdSet, $alamatIdSet, $fotoIdSet) {
            $missing = [];

            if (isset($alamatIdSet[$umkm->id_umkm])) {
                $missing[] = 'Alamat';
            }

            if (isset($teleponIdSet[$umkm->id_umkm])) {
                $missing[] = 'Telepon';
            }

            if (isset($jamIdSet[$umkm->id_umkm])) {
                $missing[] = 'Jam Buka';
            }

            if (isset($fotoIdSet[$umkm->id_umkm])) {
                $missing[] = 'Foto';
            }

            if (isset($koordinatIdSet[$umkm->id_umkm])) {
                $missing[] = 'Koordinat';
            }

            $umkm->setAttribute('missing_fields', $missing);
        });

        $pendingSubmissions = UmkmSubmission::with(['kategori', 'menuSubmissions'])
            ->where('status', 'pending')
            ->latest('created_at')
            ->take(10)
            ->get();

        $pendingMenuSubmissions = MenuSubmission::with('umkm')
            ->whereNull('umkm_submission_id')
            ->where('status', 'pending')
            ->latest('created_at')
            ->take(12)
            ->get();

        return view('admin.dashboard', compact(
            'umkmTanpaAlamat',
            'umkmTanpaFoto',
            'umkmTanpaJam',
            'stats',
            'recentUmkm',
            'recentRatings',
            'kategoriStats',
            'umkmTanpaKoordinat',
            'umkmTanpaTelepon',
            'pendingSubmissions',
            'pendingMenuSubmissions',
            'topClicks',
            'lowestClicks',
            'topClicksLabels',
            'topClicksValues',
            'lowestClicksLabels',
            'lowestClicksValues',
            'ratingCategoryLabels',
            'ratingCategoryValues',
            'wilayahLabels',
            'wilayahValues',
            'jamBukaLabels',
            'jamBukaValues',
            'umkmPerluPerbaikan'
        ));
    }

    public function approveSubmission(UmkmSubmission $submission): RedirectResponse
    {
        if ($submission->status !== 'pending') {
            return back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        DB::transaction(function () use ($submission): void {
            $lokasi = Lokasi::create([
                'latitude' => $submission->latitude,
                'longitude' => $submission->longitude,
            ]);

            $baseSlug = Str::slug($submission->nama_umkm);
            $slug = $baseSlug;
            $counter = 1;
            while (Umkm::where('slug_umkm', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }

            $umkm = Umkm::create([
                'nama_umkm' => $submission->nama_umkm,
                'slug_umkm' => $slug,
                'jam_buka' => $submission->jam_buka,
                'no_telfon' => $submission->no_telfon,
                'alamat_lengkap' => $submission->alamat_lengkap,
                'deskripsi' => $submission->deskripsi,
                'foto_umkm' => $submission->foto_umkm ?: '-',
                'id_lokasi' => $lokasi->id_lokasi,
                'id_kategori' => $submission->id_kategori,
                'source' => 'user_submission',
            ]);

            $submittedMenus = $submission->menuSubmissions()
                ->where('status', 'pending')
                ->get();

            foreach ($submittedMenus as $menuSubmission) {
                Menu::create([
                    'nama_menu' => $menuSubmission->nama_menu,
                    'harga_menu' => $menuSubmission->harga_menu,
                    'foto_menu' => $menuSubmission->foto_menu ?: '-',
                    'id_umkm' => $umkm->id_umkm,
                ]);

                $menuSubmission->update([
                    'id_umkm' => $umkm->id_umkm,
                    'status' => 'approved',
                    'reviewed_by' => auth()->id(),
                    'reviewed_at' => now(),
                    'admin_note' => 'Disetujui bersamaan dengan pengajuan UMKM.',
                ]);
            }

            $submission->update([
                'status' => 'approved',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'admin_note' => 'Disetujui oleh admin.',
            ]);
        });

        return back()->with('success', 'Pengajuan UMKM berhasil disetujui dan dipublikasikan.');
    }

    public function rejectSubmission(Request $request, UmkmSubmission $submission): RedirectResponse
    {
        if ($submission->status !== 'pending') {
            return back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $request->validate([
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $submission->update([
            'status' => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'admin_note' => $request->input('admin_note') ?: 'Ditolak oleh admin.',
        ]);

        $submission->menuSubmissions()
            ->where('status', 'pending')
            ->update([
                'status' => 'rejected',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'admin_note' => 'Ditolak otomatis karena pengajuan UMKM ditolak.',
            ]);

        return back()->with('success', 'Pengajuan UMKM telah ditolak.');
    }

    public function approveMenuSubmission(MenuSubmission $menuSubmission): RedirectResponse
    {
        if ($menuSubmission->status !== 'pending') {
            return back()->with('error', 'Pengajuan menu ini sudah diproses sebelumnya.');
        }

        if (!$menuSubmission->id_umkm) {
            return back()->with('error', 'UMKM tujuan untuk pengajuan menu ini tidak ditemukan.');
        }

        DB::transaction(function () use ($menuSubmission): void {
            Menu::create([
                'nama_menu' => $menuSubmission->nama_menu,
                'harga_menu' => $menuSubmission->harga_menu,
                'foto_menu' => $menuSubmission->foto_menu ?: '-',
                'id_umkm' => $menuSubmission->id_umkm,
                'source' => 'user_submission',
            ]);

            $menuSubmission->update([
                'status' => 'approved',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'admin_note' => 'Menu disetujui oleh admin.',
            ]);
        });

        return back()->with('success', 'Pengajuan menu berhasil disetujui.');
    }

    public function rejectMenuSubmission(Request $request, MenuSubmission $menuSubmission): RedirectResponse
    {
        if ($menuSubmission->status !== 'pending') {
            return back()->with('error', 'Pengajuan menu ini sudah diproses sebelumnya.');
        }

        $request->validate([
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $menuSubmission->update([
            'status' => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'admin_note' => $request->input('admin_note') ?: 'Menu ditolak oleh admin.',
        ]);

        return back()->with('success', 'Pengajuan menu telah ditolak.');
    }
}
