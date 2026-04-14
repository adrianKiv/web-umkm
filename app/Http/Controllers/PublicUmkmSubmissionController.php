<?php

namespace App\Http\Controllers;

use App\Models\UmkmSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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
            'foto_umkm' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'id_kategori' => ['required', 'exists:kategori,id_kategori'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        if ($request->hasFile('foto_umkm')) {
            $validated['foto_umkm'] = $request->file('foto_umkm')->store('umkm', 'public');
        }

        $validated['status'] = 'pending';
        UmkmSubmission::create($validated);

        return back()->with('success', 'Pengajuan UMKM berhasil dikirim dan sedang menunggu konfirmasi admin.');
    }
}
