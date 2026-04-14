<?php

namespace Database\Factories;

use App\Models\Rating;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Rating>
 */
class RatingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_pengulas' => $this->faker->name(),
            'nilai_rating' => $this->faker->numberBetween(1, 5),
            'komentar' => $this->faker->sentence(),
            'id_umkm' => \App\Models\Umkm::inRandomOrder()->first()->id_umkm ?? 1,
        ];
    }
}
