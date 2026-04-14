<?php

namespace Database\Factories;

use App\Models\Umkm;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Umkm>
 */
class UmkmFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_umkm' => $this->faker->company(),
            'slug_umkm' => $this->faker->unique()->slug(),
            'jam_buka' => '08:00 - 20:00',
            'no_telfon' => $this->faker->phoneNumber(),
            'alamat_lengkap' => $this->faker->address(),
            'deskripsi' => $this->faker->paragraph(),
            'foto_umkm' => 'images/' . $this->faker->uuid() . '.jpg',
            'id_lokasi' => \App\Models\Lokasi::inRandomOrder()->first()->id_lokasi ?? 1,
            'id_kategori' => \App\Models\Kategori::inRandomOrder()->first()->id_kategori ?? 1,
        ];
    }
}
