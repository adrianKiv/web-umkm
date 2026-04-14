<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelompok;
use Illuminate\Http\Request;

class KelompokAdminController extends Controller
{
    /**
     * Display a listing of groups
     */
    public function index()
    {
        $kelompoks = Kelompok::paginate(20);

        return view('admin.kelompok.index', compact('kelompoks'));
    }

    /**
     * Show the form for creating a new group
     */
    public function create()
    {
        return view('admin.kelompok.create');
    }

    /**
     * Store a newly created group
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kelompok' => 'required|unique:kelompok|max:255',
        ]);

        Kelompok::create($validated);

        return redirect()->route('admin.kelompok.index')
            ->with('success', 'Kelompok berhasil ditambahkan');
    }

    /**
     * Show group detail
     */
    public function show(Kelompok $kelompok)
    {
        return view('admin.kelompok.show', compact('kelompok'));
    }

    /**
     * Show the form for editing group
     */
    public function edit(Kelompok $kelompok)
    {
        return view('admin.kelompok.edit', compact('kelompok'));
    }

    /**
     * Update group
     */
    public function update(Request $request, Kelompok $kelompok)
    {
        $validated = $request->validate([
            'nama_kelompok' => 'required|max:255|unique:kelompok,nama_kelompok,' . $kelompok->id_kelompok . ',id_kelompok',
        ]);

        $kelompok->update($validated);

        return redirect()->route('admin.kelompok.show', $kelompok)
            ->with('success', 'Kelompok berhasil diupdate');
    }

    /**
     * Delete group
     */
    public function destroy(Kelompok $kelompok)
    {
        $kelompok->delete();

        return redirect()->route('admin.kelompok.index')
            ->with('success', 'Kelompok berhasil dihapus');
    }
}
