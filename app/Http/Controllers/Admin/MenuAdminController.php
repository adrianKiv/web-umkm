<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Umkm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MenuAdminController extends Controller
{
    /**
     * Display a listing of menu items.
     */
    public function index()
    {
        $menus = Menu::with('umkm')->latest('id_menu')->paginate(15);

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
            'nama_menu' => 'required|string|max:100',
            'harga_menu' => 'required|numeric|min:0',
            'id_umkm' => 'required|exists:umkm,id_umkm',
            'foto_menu' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $validated['foto_menu'] = '-';
        if ($request->hasFile('foto_menu')) {
            $validated['foto_menu'] = $request->file('foto_menu')->store('menu', 'public');
        }

        Menu::create($validated);

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
            'foto_menu' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $fotoMenu = $menu->foto_menu ?: '-';
        if ($request->hasFile('foto_menu')) {
            if ($menu->foto_menu && $menu->foto_menu !== '-' && Storage::disk('public')->exists($menu->foto_menu)) {
                Storage::disk('public')->delete($menu->foto_menu);
            }
            $fotoMenu = $request->file('foto_menu')->store('menu', 'public');
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
        if ($menu->foto_menu && $menu->foto_menu !== '-' && Storage::disk('public')->exists($menu->foto_menu)) {
            Storage::disk('public')->delete($menu->foto_menu);
        }

        $menu->delete();

        return redirect()->route('admin.menu.index')
            ->with('success', 'Menu berhasil dihapus.');
    }
}
