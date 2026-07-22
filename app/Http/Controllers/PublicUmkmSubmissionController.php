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
        'nama_pengusul'      => ['required', 'string', 'max:120'],
        'email_pengusul'     => ['required', 'email', 'max:160'],
        'nama_umkm'          => ['required', 'string', 'max:120'],
        'jam_buka'           => ['required', 'string', 'max:60'],
        'no_telfon'          => ['required', 'string', 'max:25'],
        'alamat_lengkap'     => ['required', 'string', 'max:2000'],
        'deskripsi'          => ['required', 'string', 'max:4000'],
        'foto_umkm'          => ['required', 'image', 'mimes:jpg,jpeg,png,gif,bmp,webp', 'max:2048'],
        'id_kategori'        => ['required', 'exists:kategori,id_kategori'],
        'latitude'           => ['required', 'numeric', 'between:-90,90'],
        'longitude'          => ['required', 'numeric', 'between:-180,180'],
        'menu_nama'          => ['nullable', 'array'],
        'menu_nama.*'        => ['nullable', 'string', 'max:100'],
        'menu_harga'         => ['nullable', 'array'],
        'menu_harga.*'       => ['nullable', 'numeric', 'min:0'],
        'menu_foto'          => ['nullable', 'array'],
        'menu_foto.*'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,bmp,webp', 'max:2048'],
        'menu_daftar_foto'   => ['nullable', 'array'],
        'menu_daftar_foto.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,bmp,webp', 'max:4096'],
    ]
    , [
        // Pesan Error Pengusul
        'nama_pengusul.required' => 'Nama pengusul wajib diisi.',
        'nama_pengusul.max'      => 'Nama pengusul maksimal 120 karakter.',
        'email_pengusul.required' => 'Email pengusul wajib diisi.',
        'email_pengusul.email'   => 'Format email pengusul tidak valid.',
        'email_pengusul.max'     => 'Email pengusul maksimal 160 karakter.',

        // Pesan Error UMKM Inti
        'nama_umkm.required'      => 'Nama UMKM wajib diisi.',
        'nama_umkm.max'           => 'Nama UMKM maksimal 120 karakter.',
        'jam_buka.required'       => 'Jam buka wajib diisi.',
        'jam_buka.max'            => 'Info jam buka maksimal 60 karakter.',
        'no_telfon.required'      => 'Nomor telepon/WhatsApp wajib diisi.',
        'no_telfon.max'           => 'Nomor telepon maksimal 25 karakter.',
        'alamat_lengkap.required' => 'Alamat lengkap wajib diisi.',
        'alamat_lengkap.max'      => 'Alamat lengkap maksimal 2000 karakter.',
        'deskripsi.required'      => 'Deskripsi UMKM wajib diisi.',
        'deskripsi.max'           => 'Deskripsi UMKM maksimal 4000 karakter.',
        'id_kategori.required'    => 'Kategori UMKM wajib dipilih.',
        'id_kategori.exists'      => 'Kategori yang dipilih tidak valid atau tidak ditemukan.',

        // Pesan Error Peta/Koordinat
        'latitude.required'       => 'Titik lokasi pada peta wajib ditentukan.',
        'latitude.numeric'        => 'Titik koordinat latitude tidak valid.',
        'longitude.required'      => 'Titik lokasi pada peta wajib ditentukan.',
        'longitude.numeric'       => 'Titik koordinat longitude tidak valid.',

        // Pesan Error Foto UMKM Utama
        'foto_umkm.required'       => 'Foto UMKM wajib diisi.',
        'foto_umkm.image'         => 'File profil UMKM harus berupa gambar (JPG, PNG, dsb).',
        'foto_umkm.mimes'         => 'Format foto UMKM yang diizinkan hanya: jpg, jpeg, png, gif, bmp, webp.',
        'foto_umkm.max'           => 'Ukuran foto profil UMKM maksimal 2 MB.',

        // Pesan Error Menu Satuan
        'menu_nama.*.max'         => 'Nama menu maksimal 100 karakter.',
        'menu_harga.*.numeric'    => 'Harga menu harus berupa angka (tanpa titik/koma ribuan).',
        'menu_harga.*.min'        => 'Harga menu tidak boleh minus.',
        'menu_foto.*.image'       => 'File foto menu satuan harus berupa gambar.',
        'menu_foto.*.mimes'       => 'Format foto menu yang diizinkan hanya: jpg, jpeg, png, gif, bmp, webp.',
        'menu_foto.*.max'         => 'Ukuran masing-masing foto menu satuan maksimal 2 MB.',

        // Pesan Error Daftar/Buku Menu
        'menu_daftar_foto.*.image' => 'File daftar menu harus berupa gambar.',
        'menu_daftar_foto.*.mimes' => 'Format daftar menu yang diizinkan hanya: jpg, jpeg, png, gif, bmp, webp.',
        'menu_daftar_foto.*.max'   => 'Ukuran foto daftar menu maksimal 4 MB per file.',
    ]
    );

    try{

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

        } catch (\Exception $e){
        return back()->with('error', 'Terjadi kesalahan sistem saat menyimpan pengajuan.');
    }
    }

    /**
     * Submit menu suggestion to existing UMKM.
     */
    public function storeMenu(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id_umkm' => ['required', 'exists:umkm,id_umkm'],
            'nama_pengusul' => ['required', 'string', 'max:120'],
            'email_pengusul' => ['required', 'email', 'max:160'],
            'menu_nama' => ['nullable', 'array'],
            'menu_nama.*' => ['nullable', 'string', 'max:100'],
            'menu_harga' => ['nullable', 'array'],
            'menu_harga.*' => ['nullable', 'numeric', 'min:0'],
            'menu_foto' => ['nullable', 'array'],
            'menu_foto.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,bmp,webp', 'max:2048'],
            'menu_daftar_foto' => ['nullable', 'array'],
            'menu_daftar_foto.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,bmp,webp', 'max:4096'],
        ], [
            // Pesan Error Pengusul
            'nama_pengusul.required' => 'Nama pengusul wajib diisi.',
            'nama_pengusul.max'      => 'Nama pengusul maksimal 120 karakter.',
            'email_pengusul.required' => 'Email pengusul wajib diisi.',
            'email_pengusul.email'   => 'Format email pengusul tidak valid.',
            'email_pengusul.max'     => 'Email pengusul maksimal 160 karakter.',
        ]);

        try{

        $targetUmkm = Umkm::findOrFail($validated['id_umkm']);

        $menuNames = $validated['menu_nama'];
        $menuPrices = $validated['menu_harga'];
        $menuFiles = $request->file('menu_foto', []);
        $maxItems = max(count($menuNames), count($menuPrices), count($menuFiles));
        $createdAny = false;

        // 2. Cek apakah form menu satuan kosong SEMUA
        $isMenuSatuanKosong = true;
        if ($request->has('menu_nama')) {
            foreach ($request->menu_nama as $nama) {
                if (!empty(trim($nama))) {
                    $isMenuSatuanKosong = false;
                    break;
                }
            }
        }

        // 3. Cek apakah input foto daftar menu (buku menu) kosong
        $isDaftarMenuKosong = true;
        if ($request->hasFile('menu_daftar_foto')) {
            foreach ($request->file('menu_daftar_foto') as $file) {
                if ($file && $file->isValid()) {
                    $isDaftarMenuKosong = false;
                    break;
                }
            }
        }

        // 4. Jika kedua opsi menu tersebut kosong, gagalkan dan kembalikan pesan error
        if ($isMenuSatuanKosong && $isDaftarMenuKosong) {
            return back()->withInput()->withErrors([
                'menu_kosong' => 'Harap isi minimal salah satu: Data menu satuan atau Upload buku/daftar menu lengkap.'
            ]);
        }

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
            throw ValidationException::withMessages([
                'menu_nama' => 'Anda belum memasukkan data menu. Isi manual minimal 1 Nama Menu atau unggah Foto Daftar Menu.',
            ]);
        }

        return back()->with('success', 'Pengajuan menu berhasil dikirim dan menunggu konfirmasi admin.');
    } catch (\Exception $e){
        return back()->with('error', 'Terjadi kesalahan sistem saat menyimpan pengajuan.');
    }
    }
}
