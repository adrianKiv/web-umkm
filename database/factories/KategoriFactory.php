<?php

namespace Database\Factories;

use App\Models\Kategori;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Kategori>
 */
class KategoriFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nama = $this->faker->word();
        return [
            'nama_kategori' => ucfirst($nama),
            'slug_kategori' => $this->faker->unique()->slug(2),
            'id_kelompok' => \App\Models\Kelompok::inRandomOrder()->first()->id_kelompok ?? 1,z
        ];
    }
}
