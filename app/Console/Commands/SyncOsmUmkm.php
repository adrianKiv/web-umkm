<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Umkm;
use App\Models\Lokasi;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class SyncOsmUmkm extends Command
{
    // Nama perintah yang akan diketik di console
    protected $signature = 'umkm:sync-osm';

    // Deskripsi perintah
    protected $description = 'Menarik data UMKM dari OpenStreetMap di sekitar UPI Bandung';

    public function handle()
    {
        $this->info('Memulai sinkronisasi data dari OpenStreetMap...');

        $lat = -6.861082410263256;
        $lng = 107.59205888361987;
        $radius = 1500;

        $query = "
            [out:json][timeout:30];
            (
              node[\"amenity\"~\"restaurant|cafe|fast_food|food_court|ice_cream\"](around:$radius,$lat,$lng);
              way[\"amenity\"~\"restaurant|cafe|fast_food|food_court|ice_cream\"](around:$radius,$lat,$lng);
            );
            out center;
        ";

        try {
            $response = Http::timeout(60)->asForm()->post("https://overpass-api.de/api/interpreter", [
                'data' => $query
            ]);

            if ($response->successful()) {
                $elements = $response->json()['elements'] ?? [];
                $bar = $this->output->createProgressBar(count($elements));
                $bar->start();

                $newCount = 0;

            foreach ($elements as $el) {
                $tags = $el['tags'] ?? [];
                $osmId = $el['id'];
                $nama = $tags['name'] ?? null;

                if (!$nama || Umkm::where('osm_id', $osmId)->exists()) {
                    $bar->advance();
                    continue;
                }

                // --- LOGIKA BARU DI SINI ---
                $latitude = $el['lat'] ?? ($el['center']['lat'] ?? null);
                $longitude = $el['lon'] ?? ($el['center']['lon'] ?? null);
                $alamatHasilMapping = $tags['addr:street'] ?? 'Kawasan UPI Bandung';

                // Jika alamat di OSM kosong, minta bantuan Nominatim API
                if ($alamatHasilMapping == 'Kawasan UPI Bandung' && $latitude && $longitude) {
                    try {
                        $geoResponse = Http::withHeaders(['User-Agent' => 'Katalog-UMKM-UPI-Tesis'])
                            ->timeout(5)
                            ->get("https://nominatim.openstreetmap.org/reverse", [
                                'format' => 'jsonv2',
                                'lat'    => $latitude,
                                'lon'    => $longitude,
                            ]);

                        if ($geoResponse->successful()) {
                            // Mengambil alamat yang lebih manusiawi
                            $alamatHasilMapping = $geoResponse->json()['display_name'] ?? $alamatHasilMapping;
                        }
                    } catch (\Exception $e) {
                        // Jika API limit atau error, biarkan pakai default
                    }
                }
                // --- AKHIR LOGIKA BARU ---

                DB::transaction(function () use ($el, $tags, $nama, $osmId, $latitude, $longitude, $alamatHasilMapping, &$newCount) {
                    if ($latitude && $longitude) {
                        $lokasi = Lokasi::create([
                            'latitude' => $latitude,
                            'longitude' => $longitude,
                        ]);

                        Umkm::create([
                            'osm_id'         => $osmId,
                            'nama_umkm'      => $nama,
                            'foto_umkm'      => '-',
                            'slug_umkm'      => Str::slug($nama) . '-' . $osmId,
                            'id_lokasi'      => $lokasi->id_lokasi,
                            'id_kategori'    => $this->mapKategori($tags['amenity'] ?? '', $nama),
                            'alamat_lengkap' => $alamatHasilMapping, // Gunakan hasil mapping tadi
                            'jam_buka'       => $tags['opening_hours'] ?? 'Belum tersedia',
                            'no_telfon'      => $tags['phone'] ?? '-',
                            'deskripsi'      => "Sinkronisasi otomatis Console (Cuisine: " . ($tags['cuisine'] ?? 'Umum') . ")",
                        ]);
                        $newCount++;
                    }
                });

                $bar->advance();
            }

                $bar->finish();
                $this->newLine();
                $this->info("Selesai! $newCount UMKM baru berhasil ditambahkan.");
            }
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
    }

    private function mapKategori($amenity, $nama)
    {
        $n = strtolower($nama);
        if (str_contains($n, 'kopi') || $amenity == 'cafe') return 7;
        if (str_contains($n, 'mie') || str_contains($n, 'bakso')) return 3;
        if (str_contains($n, 'seblak') || str_contains($n, 'aci')) return 4;
        return 1; // Nasi & Warteg
    }
}

