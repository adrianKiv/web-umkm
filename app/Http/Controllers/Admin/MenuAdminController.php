<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Umkm;
use App\Support\WebpImageUploader;
use App\Support\StorageFile;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;

class MenuAdminController extends Controller
{
    /**
     * Display a listing of menu items.
     */
    public function index(Request $request)
    {
        // Mulai query dengan relasi umkm
        $query = Menu::with('umkm')->latest('id_menu');

        // Jika ada parameter pencarian
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;

            // Cari berdasarkan nama menu ATAU nama UMKM yang berelasi
            $query->where(function($q) use ($search) {
                $q->where('nama_menu', 'like', '%' . $search . '%')
                ->orWhereHas('umkm', function($subQuery) use ($search) {
                    $subQuery->where('nama_umkm', 'like', '%' . $search . '%');
                });
            });
        }

        // Paginate dan tambahkan withQueryString() agar parameter pencarian
        // tidak hilang saat admin pindah ke halaman (page) 2, 3, dst.
        $menus = $query->paginate(15)->withQueryString();

        return view('admin.menu.index', compact('menus'));
    }

    /**
     * Show the form for creating a new menu item.
     */
    public function create()
    {
        $umkms = Umkm::orderBy('nama_umkm')->get();

        return view('admin.menu.create', compact('umkms'));
    }

    /**
     * Store a newly created menu item in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_umkm' => 'required|exists:umkm,id_umkm',
            'menu_nama' => 'nullable|array',
            'menu_nama.*' => 'nullable|string|max:100',
            'menu_harga' => 'nullable|array',
            'menu_harga.*' => 'nullable|numeric|min:0',
            'menu_foto' => 'nullable|array',
            'menu_foto.*' => 'nullable|image|mimes:jpg,jpeg,png,gif,bmp,webp|max:2048',
            'menu_daftar_foto' => 'nullable|array',
            'menu_daftar_foto.*' => 'nullable|image|mimes:jpg,jpeg,png,gif,bmp,webp|max:4096',
        ]);

        $menuNames = $request->input('menu_nama', []);
        $menuPrices = $request->input('menu_harga', []);
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

            $fotoMenu = '-';
            if ($menuFile) {
                $fotoMenu = WebpImageUploader::store($menuFile, 'menu', 'menu');
            }

            Menu::create([
                'nama_menu' => $namaMenu,
                'harga_menu' => $hargaMenu,
                'foto_menu' => $fotoMenu,
                'id_umkm' => $validated['id_umkm'],
            ]);

            $createdAny = true;
        }

        foreach ($request->file('menu_daftar_foto', []) as $daftarFotoFile) {
            if (!$daftarFotoFile) {
                continue;
            }

            Menu::create([
                'nama_menu' => Menu::FOTO_DAFTAR_MENU_SENTINEL,
                'harga_menu' => 0,
                'foto_menu' => WebpImageUploader::store($daftarFotoFile, 'menu', 'menu'),
                'id_umkm' => $validated['id_umkm'],
            ]);

            $createdAny = true;
        }

        if (!$createdAny) {
            throw ValidationException::withMessages([
                'menu_nama' => 'Isi minimal satu menu atau unggah minimal satu foto daftar menu sebelum menyimpan.',
            ]);
        }

        return redirect()->route('admin.menu.index')
            ->with('success', 'Menu berhasil ditambahkan.');
    }

    /**
     * Display the specified menu item.
     */
    public function show(Menu $menu)
    {
        $menu->load('umkm');

        return view('admin.menu.show', compact('menu'));
    }

    /**
     * Show the form for editing the specified menu item.
     */
    public function edit(Menu $menu)
    {
        $umkms = Umkm::orderBy('nama_umkm')->get();

        return view('admin.menu.edit', compact('menu', 'umkms'));
    }

    /**
     * Update the specified menu item in storage.
     */
    public function update(Request $request, Menu $menu)
    {
        $validated = $request->validate([
            'nama_menu' => 'required|string|max:100',
            'harga_menu' => 'required|numeric|min:0',
            'id_umkm' => 'required|exists:umkm,id_umkm',
            'foto_menu' => 'nullable|image|mimes:jpg,jpeg,png,gif,bmp,webp|max:2048',
        ]);

        $fotoMenu = $menu->foto_menu ?: '-';
        if ($request->hasFile('foto_menu')) {
            StorageFile::deleteIfExists($menu->foto_menu);
            $fotoMenu = WebpImageUploader::store($request->file('foto_menu'), 'menu', 'menu');
        }

        $menu->update([
            'nama_menu' => $validated['nama_menu'],
            'harga_menu' => $validated['harga_menu'],
            'id_umkm' => $validated['id_umkm'],
            'foto_menu' => $fotoMenu,
        ]);

        return redirect()->route('admin.menu.show', $menu)
            ->with('success', 'Menu berhasil diperbarui.');
    }

    /**
     * Remove the specified menu item from storage.
     */
    public function destroy(Menu $menu)
    {
        StorageFile::deleteIfExists($menu->foto_menu);

        $menu->delete();

        return redirect()->route('admin.menu.index')
            ->with('success', 'Menu berhasil dihapus.');
    }
}
