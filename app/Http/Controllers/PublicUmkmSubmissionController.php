<?php

namespace App\Http\Controllers;

use App\Models\MenuSubmission;
use App\Models\Umkm;
use App\Models\UmkmSubmission;
use App\Support\WebpImageUploader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PublicUmkmSubmissionController extends Controller
{
    /**
     * Store UMKM submission from public pages. Data is queued for admin review.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_pengusul' => ['required', 'string', 'max:120'],
            'email_pengusul' => ['nullable', 'email', 'max:160'],
            'nama_umkm' => ['required', 'string', 'max:120'],
            'jam_buka' => ['required', 'string', 'max:60'],
            'no_telfon' => ['required', 'string', 'max:25'],
            'alamat_lengkap' => ['required', 'string', 'max:2000'],
            'deskripsi' => ['required', 'string', 'max:4000'],
            'foto_umkm' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,bmp,webp', 'max:2048'],
            'id_kategori' => ['required', 'exists:kategori,id_kategori'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'menu_nama' => ['nullable', 'array'],
            'menu_nama.*' => ['nullable', 'string', 'max:100'],
            'menu_harga' => ['nullable', 'array'],
            'menu_harga.*' => ['nullable', 'numeric', 'min:0'],
            'menu_foto' => ['nullable', 'array'],
            'menu_foto.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,bmp,webp', 'max:2048'],
            'menu_daftar_foto' => ['nullable', 'array'],
            'menu_daftar_foto.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,bmp,webp', 'max:4096'],
        ]);

        if ($request->hasFile('foto_umkm')) {
            $validated['foto_umkm'] = WebpImageUploader::store($request->file('foto_umkm'), 'umkm', 'umkm');
        }

        $menuNames = $request->input('menu_nama', []);
        $menuPrices = $request->input('menu_harga', []);
        $menuFiles = $request->file('menu_foto', []);
        $maxItems = max(count($menuNames), count($menuPrices), count($menuFiles));
        $menuDrafts = [];

        for ($i = 0; $i < $maxItems; $i++) {
            $namaMenu = trim((string) ($menuNames[$i] ?? ''));
            $hargaMenuRaw = $menuPrices[$i] ?? null;
            $menuFile = $menuFiles[$i] ?? null;
            $hasAnyValue = $namaMenu !== '' || ($hargaMenuRaw !== null && $hargaMenuRaw !== '') || $menuFile;

            if (!$hasAnyValue) {
                continue;
            }

            if ($namaMenu === '' || $hargaMenuRaw === null || $hargaMenuRaw === '') {
                throw ValidationException::withMessages([
                    'menu_nama' => 'Setiap menu yang diisi harus memiliki nama menu dan harga menu.',
                ]);
            }

            $menuDrafts[] = [
                'nama_menu' => $namaMenu,
                'harga_menu' => $hargaMenuRaw,
                'file' => $menuFile,
            ];
        }

        $validated['status'] = 'pending';
        $submission = UmkmSubmission::create($validated);

        foreach ($menuDrafts as $menuDraft) {
            $menuFile = $menuDraft['file'];

            $fotoPath = null;
            if ($menuFile) {
                $fotoPath = WebpImageUploader::store($menuFile, 'menu', 'menu');
            }

            MenuSubmission::create([
                'umkm_submission_id' => $submission->id,
                'nama_pengusul' => $validated['nama_pengusul'],
                'email_pengusul' => $validated['email_pengusul'] ?? null,
                'nama_menu' => $menuDraft['nama_menu'],
                'harga_menu' => $menuDraft['harga_menu'],
                'foto_menu' => $fotoPath,
                'status' => 'pending',
            ]);
        }

        foreach ($request->file('menu_daftar_foto', []) as $daftarFotoFile) {
            if (!$daftarFotoFile) {
                continue;
            }

            MenuSubmission::create([
                'umkm_submission_id' => $submission->id,
                'nama_pengusul' => $validated['nama_pengusul'],
                'email_pengusul' => $validated['email_pengusul'] ?? null,
                'nama_menu' => MenuSubmission::FOTO_DAFTAR_MENU_SENTINEL,
                'harga_menu' => 0,
                'foto_menu' => WebpImageUploader::store($daftarFotoFile, 'menu', 'menu'),
                'status' => 'pending',
            ]);
        }

        return back()->with('success', 'Pengajuan UMKM berhasil dikirim dan sedang menunggu konfirmasi admin.');
    }

    /**
     * Submit menu suggestion to existing UMKM.
     */
    public function storeMenu(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id_umkm' => ['required', 'exists:umkm,id_umkm'],
            'nama_pengusul' => ['required', 'string', 'max:120'],
            'email_pengusul' => ['nullable', 'email', 'max:160'],
            'menu_nama' => ['nullable', 'array'],
            'menu_nama.*' => ['nullable', 'string', 'max:100'],
            'menu_harga' => ['nullable', 'array'],
            'menu_harga.*' => ['nullable', 'numeric', 'min:0'],
            'menu_foto' => ['nullable', 'array'],
            'menu_foto.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,bmp,webp', 'max:2048'],
            'menu_daftar_foto' => ['nullable', 'array'],
            'menu_daftar_foto.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,bmp,webp', 'max:4096'],
        ]);

        $targetUmkm = Umkm::findOrFail($validated['id_umkm']);

        $menuNames = $validated['menu_nama'];
        $menuPrices = $validated['menu_harga'];
        $menuFiles = $request->file('menu_foto', []);
        $maxItems = max(count($menuNames), count($menuPrices), count($menuFiles));
        $createdAny = false;

        for ($i = 0; $i < $maxItems; $i++) {
            $namaMenu = trim((string) ($menuNames[$i] ?? ''));
            $hargaMenu = $menuPrices[$i] ?? null;
            $menuFile = $menuFiles[$i] ?? null;

            if ($namaMenu === '' && ($hargaMenu === null || $hargaMenu === '') && !$menuFile) {
                continue;
            }

            if ($namaMenu === '' || $hargaMenu === null || $hargaMenu === '') {
                throw ValidationException::withMessages([
                    'menu_nama' => 'Setiap baris menu harus memiliki nama menu dan harga menu.',
                ]);
            }

            $fotoPath = null;
            if ($menuFile) {
                $fotoPath = WebpImageUploader::store($menuFile, 'menu', 'menu');
            }

            MenuSubmission::create([
                'id_umkm' => $targetUmkm->id_umkm,
                'nama_pengusul' => $validated['nama_pengusul'],
                'email_pengusul' => $validated['email_pengusul'] ?? null,
                'nama_menu' => $namaMenu,
                'harga_menu' => $hargaMenu,
                'foto_menu' => $fotoPath,
                'status' => 'pending',
            ]);

            $createdAny = true;
        }

        if (!$createdAny) {
            foreach ($request->file('menu_daftar_foto', []) as $daftarFotoFile) {
                if (!$daftarFotoFile) {
                    continue;
                }

                MenuSubmission::create([
                    'id_umkm' => $targetUmkm->id_umkm,
                    'nama_pengusul' => $validated['nama_pengusul'],
                    'email_pengusul' => $validated['email_pengusul'] ?? null,
                    'nama_menu' => MenuSubmission::FOTO_DAFTAR_MENU_SENTINEL,
                    'harga_menu' => 0,
                    'foto_menu' => WebpImageUploader::store($daftarFotoFile, 'menu', 'menu'),
                    'status' => 'pending',
                ]);

                $createdAny = true;
            }
        }

        if (!$createdAny) {
            return back()->with('error', 'Isi minimal satu menu atau unggah minimal satu foto daftar menu sebelum mengajukan.');
        }

        return back()->with('success', 'Pengajuan menu berhasil dikirim dan menunggu konfirmasi admin.');
    }
}
