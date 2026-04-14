<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Umkm;
use App\Models\Kategori;
use App\Models\Lokasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UmkmAdminController extends Controller
{
    /**
     * Display a listing of UMKM
     */
    public function index()
    {
        $umkms = Umkm::with(['kategori', 'lokasi', 'rating'])
            ->paginate(15);

        return view('admin.umkm.index', compact('umkms'));
    }

    /**
     * Show the form for creating a new UMKM
     */
    public function create()
    {
        $kategoris = Kategori::all();

        return view('admin.umkm.create', compact('kategoris'));
    }

    /**
     * Store a newly created UMKM
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_umkm' => 'required|unique:umkm|max:255',
            'slug_umkm' => 'nullable|unique:umkm|max:255',
            'jam_buka' => 'required|max:100',
            'no_telfon' => 'nullable|max:20',
            'alamat_lengkap' => 'required|max:500',
            'deskripsi' => 'nullable|max:1000',
            'foto_umkm' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'id_kategori' => 'required|exists:kategori,id_kategori',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $lokasi = Lokasi::create([
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
        ]);

        if (!$validated['slug_umkm']) {
            $validated['slug_umkm'] = Str::slug($validated['nama_umkm']);
        }

        $fotoUmkm = '-';
        if ($request->hasFile('foto_umkm')) {
            $fotoUmkm = $request->file('foto_umkm')->store('umkm', 'public');
        }

        Umkm::create([
            'nama_umkm' => $validated['nama_umkm'],
            'slug_umkm' => $validated['slug_umkm'],
            'jam_buka' => $validated['jam_buka'],
            'no_telfon' => $validated['no_telfon'] ?? '-',
            'alamat_lengkap' => $validated['alamat_lengkap'],
            'deskripsi' => $validated['deskripsi'] ?? '-',
            'foto_umkm' => $fotoUmkm,
            'id_kategori' => $validated['id_kategori'],
            'id_lokasi' => $lokasi->id_lokasi,
        ]);

        return redirect()->route('admin.umkm.index')
            ->with('success', 'UMKM berhasil ditambahkan');
    }

    /**
     * Show UMKM detail
     */
    public function show(Umkm $umkm)
    {
        $umkm->load(['kategori', 'lokasi', 'rating', 'menu']);

        return view('admin.umkm.show', compact('umkm'));
    }

    /**
     * Show the form for editing UMKM
     */
    public function edit(Umkm $umkm)
    {
        $kategoris = Kategori::all();

        return view('admin.umkm.edit', compact('umkm', 'kategoris'));
    }

    /**
     * Update UMKM
     */
    public function update(Request $request, Umkm $umkm)
    {
        $validated = $request->validate([
            'nama_umkm' => 'required|max:255|unique:umkm,nama_umkm,' . $umkm->id_umkm . ',id_umkm',
            'slug_umkm' => 'nullable|max:255|unique:umkm,slug_umkm,' . $umkm->id_umkm . ',id_umkm',
            'jam_buka' => 'required|max:100',
            'no_telfon' => 'nullable|max:20',
            'alamat_lengkap' => 'required|max:500',
            'deskripsi' => 'nullable|max:1000',
            'foto_umkm' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'id_kategori' => 'required|exists:kategori,id_kategori',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        if ($umkm->lokasi) {
            $umkm->lokasi->update([
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
            ]);
        } else {
            $lokasi = Lokasi::create([
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
            ]);
            $umkm->id_lokasi = $lokasi->id_lokasi;
        }

        if (!$validated['slug_umkm']) {
            $validated['slug_umkm'] = Str::slug($validated['nama_umkm']);
        }

        $fotoUmkm = $umkm->foto_umkm ?: '-';
        if ($request->hasFile('foto_umkm')) {
            if ($umkm->foto_umkm && $umkm->foto_umkm !== '-' && Storage::disk('public')->exists($umkm->foto_umkm)) {
                Storage::disk('public')->delete($umkm->foto_umkm);
            }
            $fotoUmkm = $request->file('foto_umkm')->store('umkm', 'public');
        }

        $umkm->update([
            'nama_umkm' => $validated['nama_umkm'],
            'slug_umkm' => $validated['slug_umkm'],
            'jam_buka' => $validated['jam_buka'],
            'no_telfon' => $validated['no_telfon'] ?? '-',
            'alamat_lengkap' => $validated['alamat_lengkap'],
            'deskripsi' => $validated['deskripsi'] ?? '-',
            'foto_umkm' => $fotoUmkm,
            'id_kategori' => $validated['id_kategori'],
            'id_lokasi' => $umkm->id_lokasi,
        ]);

        return redirect()->route('admin.umkm.show', $umkm)
            ->with('success', 'UMKM berhasil diupdate');
    }

    /**
     * Delete UMKM
     */
    public function destroy(Umkm $umkm)
    {
        $umkm->load('menu');

        foreach ($umkm->menu as $menu) {
            if ($menu->foto_menu && $menu->foto_menu !== '-' && Storage::disk('public')->exists($menu->foto_menu)) {
                Storage::disk('public')->delete($menu->foto_menu);
            }
            $menu->delete();
        }

        if ($umkm->foto_umkm && $umkm->foto_umkm !== '-' && Storage::disk('public')->exists($umkm->foto_umkm)) {
            Storage::disk('public')->delete($umkm->foto_umkm);
        }

        $umkm->delete();

        return redirect()->route('admin.umkm.index')
            ->with('success', 'UMKM berhasil dihapus');
    }
}
