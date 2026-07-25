<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Models\Kelompok;
use Illuminate\Http\Request;

class KategoriAdminController extends Controller
{
    /**
     * Display a listing of categories
     */
    public function index()
    {
        $kategoris = Kategori::with('kelompok')->paginate(20);
                // Jika ada inputan pencarian
        if ($request->has('search')) {
            $search = $request->search;
            $kategoris = $kategoris->where('nama_kategori', 'like', '%' . $search . '%');
        }

        return view('admin.kategori.index', compact('kategoris'));
    }

    /**
     * Show the form for creating a new category
     */
    public function create()
    {
        $kelompoks = Kelompok::all();

        return view('admin.kategori.create', compact('kelompoks'));
    }

    /**
     * Store a newly created category
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kategori' => 'required|unique:kategori|max:255',
            'slug_kategori' => 'nullable|unique:kategori|max:255',
            'id_kelompok' => 'required|exists:kelompok,id_kelompok',
        ]);

        if (!$validated['slug_kategori']) {
            $validated['slug_kategori'] = \Str::slug($validated['nama_kategori']);
        }

        Kategori::create($validated);

        return redirect()->route('admin.kategori.index')
            ->with('success', 'Kategori berhasil ditambahkan');
    }

    /**
     * Show category detail
     */
    public function show(Kategori $kategori)
    {
        $kategori->load('kelompok');

        return view('admin.kategori.show', compact('kategori'));
    }

    /**
     * Show the form for editing category
     */
    public function edit(Kategori $kategori)
    {
        $kelompoks = Kelompok::all();

        return view('admin.kategori.edit', compact('kategori', 'kelompoks'));
    }

    /**
     * Update category
     */
    public function update(Request $request, Kategori $kategori)
    {
        $validated = $request->validate([
            'nama_kategori' => 'required|max:255|unique:kategori,nama_kategori,' . $kategori->id_kategori . ',id_kategori',
            'slug_kategori' => 'nullable|max:255|unique:kategori,slug_kategori,' . $kategori->id_kategori . ',id_kategori',
            'id_kelompok' => 'required|exists:kelompok,id_kelompok',
        ]);

        if (!$validated['slug_kategori']) {
            $validated['slug_kategori'] = \Str::slug($validated['nama_kategori']);
        }

        $kategori->update($validated);

        return redirect()->route('admin.kategori.show', $kategori)
            ->with('success', 'Kategori berhasil diupdate');
    }

    /**
     * Delete category
     */
    public function destroy(Kategori $kategori)
    {
        $kategori->delete();

        return redirect()->route('admin.kategori.index')
            ->with('success', 'Kategori berhasil dihapus');
    }
}
