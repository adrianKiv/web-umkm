<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lokasi;
use Illuminate\Http\Request;

class LokasiAdminController extends Controller
{
    /**
     * Display a listing of locations
     */
    public function index()
    {
        $lokasis = Lokasi::paginate(20);

        return view('admin.lokasi.index', compact('lokasis'));
    }

    /**
     * Show the form for creating a new location
     */
    public function create()
    {
        return view('admin.lokasi.create');
    }

    /**
     * Store a newly created location
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        Lokasi::create($validated);

        return redirect()->route('admin.lokasi.index')
            ->with('success', 'Lokasi berhasil ditambahkan');
    }

    /**
     * Show location detail
     */
    public function show(Lokasi $lokasi)
    {
        return view('admin.lokasi.show', compact('lokasi'));
    }

    /**
     * Show the form for editing location
     */
    public function edit(Lokasi $lokasi)
    {
        return view('admin.lokasi.edit', compact('lokasi'));
    }

    /**
     * Update location
     */
    public function update(Request $request, Lokasi $lokasi)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $lokasi->update($validated);

        return redirect()->route('admin.lokasi.show', $lokasi)
            ->with('success', 'Lokasi berhasil diupdate');
    }

    /**
     * Delete location
     */
    public function destroy(Lokasi $lokasi)
    {
        $lokasi->delete();

        return redirect()->route('admin.lokasi.index')
            ->with('success', 'Lokasi berhasil dihapus');
    }
}
