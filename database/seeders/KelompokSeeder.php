<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KelompokSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Kelompok::create(['nama_kelompok' => 'Makanan']);
        \App\Models\Kelompok::create(['nama_kelompok' => 'Minuman']);
        \App\Models\Kelompok::create(['nama_kelompok' => 'Camilan']);
    }
}
