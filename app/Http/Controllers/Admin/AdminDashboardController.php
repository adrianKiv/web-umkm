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

        // 1. Cek UMKM tanpa koordinat lokasi
        // Kondisi: id_lokasi kosong ATAU latitude/longitude di tabel lokasi kosong
        $umkmTanpaKoordinat = Umkm::whereNull('id_lokasi')
            ->orWhereHas('lokasi', function ($query) {
                $query->whereNull('latitude')
                    ->orWhere('latitude', '')
                    ->orWhereNull('longitude')
                    ->orWhere('longitude', '');
            })->count();

        // 2. Cek UMKM tanpa nomor telepon
        $umkmTanpaTelepon = Umkm::whereNull('no_telfon')
            ->orWhere('no_telfon', '-')
            ->orWhere('no_telfon', '')
            ->count();

        $umkmTanpaJam = Umkm::whereNull('jam_buka')
            ->orWhere('jam_buka', '-')
            ->orWhere('jam_buka', '')
            ->count();

        $umkmTanpaAlamat = Umkm::whereNull('alamat_lengkap')
            ->orWhere('alamat_lengkap', '-')
            ->orWhere('alamat_lengkap', '')
            ->count();

        $umkmTanpaFoto = Umkm::whereNull('foto_umkm')
            ->orWhere('foto_umkm', '-')
            ->orWhere('foto_umkm', '')
            ->count();

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
            'pendingMenuSubmissions'
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
