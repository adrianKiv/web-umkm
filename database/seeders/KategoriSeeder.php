<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kategori; // 1. Tambahkan import Model
use Illuminate\Support\Str; // 2. Tambahkan import Helper Str

class KategoriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kategoris = [
            ['nama_kategori' => 'Nasi & Warteg'],
            ['nama_kategori' => 'Ayam & Bebek'],
            ['nama_kategori' => 'Mie & Bakso'],
            ['nama_kategori' => 'Seblak & Baso Aci'],
            ['nama_kategori' => 'Gorengan & Batagor'],
            ['nama_kategori' => 'Martabak & Roti'],
            ['nama_kategori' => 'Kopi & Cafe'],
            ['nama_kategori' => 'Teh & Susu'],
            ['nama_kategori' => 'Jus & Buah'],
            ['nama_kategori' => 'Western & Fastfood'],
        ];

        foreach ($kategoris as $cat) {
            // Ambil string nama dari array
            $nama = $cat['nama_kategori'];

            Kategori::create([
                'nama_kategori' => $nama,
                'slug_kategori' => Str::slug($nama), // Sekarang $nama adalah string murni
                'id_kelompok'   => 1,
            ]);
        }
    }
}
