<?php

use App\Services\RekomendasiClient;
use Illuminate\Contracts\Console\Kernel;

/**
 * Verifikasi App\Services\RekomendasiClient tanpa test suite Laravel.
 *
 * Kenapa bukan Pest: suite proyek memakai RefreshDatabase, dan migration-nya
 * (`->primary()->autoIncrement()` di SQLite) gagal di driver sqlite. Itu
 * masalah lama, bukan efek perubahan ini. Jadi klien diuji langsung.
 *
 * Jalankan dari root project:
 *   php go-umkm/parity/verify_client.php php
 *   php go-umkm/parity/verify_client.php go    http://127.0.0.1:8099
 *   php go-umkm/parity/verify_client.php go    http://127.0.0.1:8098   (server 500)
 *   php go-umkm/parity/verify_client.php go    http://127.0.0.1:9      (mati)
 */
ini_set('display_errors', 'stderr');

require __DIR__.'/../../vendor/autoload.php';

$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$engine = $argv[1] ?? 'php';
$url = $argv[2] ?? 'http://127.0.0.1:8099';

config([
    'services.rekomendasi.engine' => $engine,
    'services.rekomendasi.url' => $url,
    'services.rekomendasi.key' => 'kunci-uji',
    'services.rekomendasi.timeout' => 1.0,
]);

$client = app(RekomendasiClient::class);

$mulai = microtime(true);
$hasil = $client->rekomendasi([1, 7], 3, 'sesi-uji');

echo json_encode([
    'engine' => $engine,
    'url' => $url,
    'null' => $hasil === null,
    'top' => $hasil['top_category_ids'] ?? null,
    'id_umkm' => $hasil['id_umkm'] ?? null,
    'skor' => $hasil['skor_kategori'] ?? null,
    'durasi_ms' => round((microtime(true) - $mulai) * 1000, 1),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), "\n";
