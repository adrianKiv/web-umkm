<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Umkm;
use App\Models\Kategori;
use App\Models\Lokasi;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Support\WebpImageUploader;
use App\Support\StorageFile;

class UmkmAdminController extends Controller
{
    /**
     * Display a listing of UMKM
     */
    public function index(Request $request)
    {

        $query = Umkm::with(['kategori', 'lokasi', 'rating']);

        // Jika ada inputan pencarian
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('nama_umkm', 'like', '%' . $search . '%');
            // Anda juga bisa menambahkan OR WHERE di sini jika ingin mencari berdasarkan kategori
        }

        $umkms = $query->paginate(15)->withQueryString();

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
            'foto_umkm' => 'nullable|image|mimes:jpg,jpeg,png,gif,bmp,webp|max:2048',
            'id_kategori' => 'required|exists:kategori,id_kategori',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $fotoUmkm = '-';
        if ($request->hasFile('foto_umkm')) {
            $fotoUmkm = WebpImageUploader::store($request->file('foto_umkm'), 'umkm', 'umkm');
        }

        try {
            DB::transaction(function () use ($validated, $fotoUmkm) {
                $lokasi = Lokasi::create([
                    'latitude' => $validated['latitude'],
                    'longitude' => $validated['longitude'],
                ]);

                $slugUmkm = $validated['slug_umkm'] ?: Str::slug($validated['nama_umkm']);

                Umkm::create([
                    'nama_umkm' => $validated['nama_umkm'],
                    'slug_umkm' => $slugUmkm,
                    'jam_buka' => $validated['jam_buka'],
                    'no_telfon' => $validated['no_telfon'] ?? '-',
                    'alamat_lengkap' => $validated['alamat_lengkap'],
                    'deskripsi' => $validated['deskripsi'] ?? '-',
                    'foto_umkm' => $fotoUmkm,
                    'id_kategori' => $validated['id_kategori'],
                    'id_lokasi' => $lokasi->id_lokasi,
                ]);
            });
        } catch (\Throwable $throwable) {
            StorageFile::deleteIfExists($fotoUmkm);
            throw $throwable;
        }

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
            'foto_umkm' => 'nullable|image|mimes:jpg,jpeg,png,gif,bmp,webp|max:2048',
            'id_kategori' => 'required|exists:kategori,id_kategori',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $oldFotoUmkm = $umkm->foto_umkm;
        $newFotoUmkm = $oldFotoUmkm ?: '-';

        if ($request->hasFile('foto_umkm')) {
            $newFotoUmkm = WebpImageUploader::store($request->file('foto_umkm'), 'umkm', 'umkm');
        }

        try {
            DB::transaction(function () use ($umkm, $validated, $newFotoUmkm) {
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

                $slugUmkm = $validated['slug_umkm'] ?: Str::slug($validated['nama_umkm']);

                $umkm->update([
                    'nama_umkm' => $validated['nama_umkm'],
                    'slug_umkm' => $slugUmkm,
                    'jam_buka' => $validated['jam_buka'],
                    'no_telfon' => $validated['no_telfon'] ?? '-',
                    'alamat_lengkap' => $validated['alamat_lengkap'],
                    'deskripsi' => $validated['deskripsi'] ?? '-',
                    'foto_umkm' => $newFotoUmkm,
                    'id_kategori' => $validated['id_kategori'],
                    'id_lokasi' => $umkm->id_lokasi,
                ]);
            });
        } catch (\Throwable $throwable) {
            if ($request->hasFile('foto_umkm')) {
                StorageFile::deleteIfExists($newFotoUmkm);
            }
            throw $throwable;
        }

        if ($request->hasFile('foto_umkm')) {
            StorageFile::deleteIfExists($oldFotoUmkm);
        }

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
            StorageFile::deleteIfExists($menu->foto_menu);
            $menu->delete();
        }

        StorageFile::deleteIfExists($umkm->foto_umkm);

        $umkm->delete();

        return redirect()->route('admin.umkm.index')
            ->with('success', 'UMKM berhasil dihapus');
    }
}
