<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LokasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pusat UPI
        $centerLat = -6.860381961433996;
        $centerLng = 107.59100874311498;
        $earthRadiusKm = 6371;
        $faker = \Faker\Factory::create();

        for ($i = 0; $i < 1; $i++) {
            $distanceMeters = $faker->randomFloat(6, 0.05, 1.0) * 1000; // 50m-1000m
            $bearing = deg2rad($faker->numberBetween(0, 359));
            $distanceKm = $distanceMeters / 1000;

            $lat1 = deg2rad($centerLat);
            $lng1 = deg2rad($centerLng);

            $lat2 = asin(sin($lat1) * cos($distanceKm / $earthRadiusKm) + cos($lat1) * sin($distanceKm / $earthRadiusKm) * cos($bearing));
            $lng2 = $lng1 + atan2(sin($bearing) * sin($distanceKm / $earthRadiusKm) * cos($lat1), cos($distanceKm / $earthRadiusKm) - sin($lat1) * sin($lat2));

            $lat2 = rad2deg($lat2);
            $lng2 = rad2deg($lng2);

            \App\Models\Lokasi::create([
                'latitude' => round($lat2, 15),
                'longitude' => round($lng2, 15),
            ]);
        }
    }
}
