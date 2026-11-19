<?php

/**
 * Sisi PHP dari uji paritas Sprint 0.
 *
 * Menyalin persis logika production:
 *   - App\Http\Controllers\OsmController::getKategoriId()
 *   - Str::slug($nama) . '-' . $osmId
 *
 * Jalankan dari root project:
 *   php go-umkm/parity/php_fixture.php go-umkm/parity/samples.json
 */

// Warning runtime (proc_open di vendor) jangan sampai merusak JSON di stdout.
ini_set('display_errors', 'stderr');

require __DIR__ . '/../../vendor/autoload.php';

use Illuminate\Support\Str;

function getKategoriId(string $amenity, string $nama): int
{
    $namaLower = strtolower($nama);

    if (strpos($namaLower, 'kopi') !== false || $amenity === 'cafe') {
        return 7; // Kopi & Cafe
    }
    if (strpos($namaLower, 'mie') !== false || strpos($namaLower, 'bakso') !== false) {
        return 3; // Mie & Bakso
    }
    if (strpos($namaLower, 'seblak') !== false || strpos($namaLower, 'aci') !== false) {
        return 4; // Seblak & Baci
    }
    if (strpos($namaLower, 'ayam') !== false || $amenity === 'fast_food') {
        return 2; // Ayam / Fastfood
    }

    return 1; // Default: Nasi & Warteg
}

$path = $argv[1] ?? 'go-umkm/parity/samples.json';
$samples = json_decode(file_get_contents($path), true);

if (!is_array($samples)) {
    fwrite(STDERR, "gagal parse {$path}\n");
    exit(1);
}

$out = [];
foreach ($samples as $s) {
    $nama = $s['nama'];
    $amenity = $s['amenity'];
    $osmId = (int) $s['osm_id'];

    $out[] = [
        'nama' => $nama,
        'amenity' => $amenity,
        'kategori' => getKategoriId($amenity, $nama),
        'slug' => Str::slug($nama) . '-' . $osmId,
    ];
}

echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), "\n";
