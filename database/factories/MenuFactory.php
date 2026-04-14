<?php

namespace Database\Factories;

use App\Models\Menu;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Menu>
 */
class MenuFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_menu' => $this->faker->word(),
            'harga_menu' => $this->faker->randomFloat(2, 10000, 50000),
            'foto_menu' => 'images/' . $this->faker->uuid() . '.jpg',
            'id_umkm' => \App\Models\Umkm::inRandomOrder()->first()->id_umkm ?? 1,
        ];
    }
}
