<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UmkmSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan data lokasi dan kategori sudah ada terlebih dahulu
        \App\Models\Umkm::factory()->count(1)->create();
    }
}
