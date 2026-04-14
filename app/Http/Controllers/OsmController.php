<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Umkm;
use App\Models\Lokasi;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class OsmController extends Controller
{
    public function sinkronisasiOsm()
    {
        // 1. Koordinat Pusat UPI & Radius (1km)
        $lat = -6.860381961433996;
        $lng = 107.59100874311498;
        $radius = 1000;

        // 2. Query Overpass (Filter hanya yang memiliki Tag 'amenity' dan 'name')
        $query = "
            [out:json][timeout:25];
            (
              node[\"amenity\"~\"restaurant|cafe|fast_food|food_court|ice_cream\"](around:$radius,$lat,$lng);
              way[\"amenity\"~\"restaurant|cafe|fast_food|food_court|ice_cream\"](around:$radius,$lat,$lng);
            );
            out center;
        ";

        try {
            // Request ke Server Overpass
            $response = Http::timeout(60)->asForm()->post("https://overpass-api.de/api/interpreter", [
                'data' => $query
            ]);

            if ($response->successful()) {
                $elements = $response->json()['elements'] ?? [];
                $successCount = 0;
                $duplicateCount = 0;

                foreach ($elements as $el) {
                    $tags = $el['tags'] ?? [];
                    $osmId = $el['id'];
                    $nama = $tags['name'] ?? null;

                    // Lewati jika data tidak punya nama (seperti di gambar tadi)
                    if (!$nama) continue;

                    // Cek duplikasi berdasarkan osm_id agar database tidak bengkak
                    if (Umkm::where('osm_id', $osmId)->exists()) {
                        $duplicateCount++;
                        continue;
                    }

                    // Gunakan Transaction agar data Lokasi dan UMKM masuk secara bersamaan (Atomicity)
                    DB::transaction(function () use ($el, $tags, $nama, $osmId, &$successCount) {
                        // Ambil Koordinat (lat/lon untuk node, center untuk way)
                        $latitude = $el['lat'] ?? ($el['center']['lat'] ?? null);
                        $longitude = $el['lon'] ?? ($el['center']['lon'] ?? null);

                        if ($latitude && $longitude) {
                            // A. Simpan ke Tabel Lokasi
                            $lokasi = Lokasi::create([
                                'latitude' => $latitude,
                                'longitude' => $longitude,
                            ]);

                            // B. Tentukan ID Kategori (Mapping Sederhana)
                            $idKategori = $this->getKategoriId($tags['amenity'] ?? '', $nama);

                            // C. Simpan ke Tabel UMKM
                            Umkm::create([
                                'osm_id'         => $osmId,
                                'source'         => 'OSM',
                                'nama_umkm'      => $nama,
                                'slug_umkm'      => Str::slug($nama) . '-' . $osmId,
                                'id_lokasi'      => $lokasi->id_lokasi,
                                'id_kategori'    => $idKategori,
                                'alamat_lengkap' => $tags['addr:street'] ?? 'Kawasan sekitar UPI Bandung',
                                'jam_buka'       => $tags['opening_hours'] ?? 'Informasi jam buka belum tersedia',
                                'no_telfon'      => $tags['phone'] ?? $tags['contact:phone'] ?? '-',
                                'deskripsi'      => "Data otomatis dari OpenStreetMap (Cuisine: " . ($tags['cuisine'] ?? 'Umum') . ")",
                                'foto_umkm'      => $tags['image'] ?? '',
                            ]);

                            $successCount++;
                        }
                    });
                }

                return response()->json([
                    'status' => 'success',
                    'message' => "Sinkronisasi selesai!",
                    'data_baru' => $successCount,
                    'data_duplikat_diabaikan' => $duplicateCount
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logika untuk memetakan Amenity OSM ke Tabel Kategori kamu (ID 1-10)
     */
    private function getKategoriId($amenity, $nama)
    {
        $namaLower = strtolower($nama);

        if (strpos($namaLower, 'kopi') !== false || $amenity == 'cafe') return 7; // Kopi & Cafe
        if (strpos($namaLower, 'mie') !== false || strpos($namaLower, 'bakso') !== false) return 3; // Mie & Bakso
        if (strpos($namaLower, 'seblak') !== false || strpos($namaLower, 'aci') !== false) return 4; // Seblak & Baci
        if (strpos($namaLower, 'ayam') !== false || $amenity == 'fast_food') return 2; // Ayam / Fastfood

        return 1; // Default: Nasi & Warteg
    }
}
