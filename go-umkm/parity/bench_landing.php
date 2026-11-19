<?php

/**
 * Benchmark jalur landing() tanpa HTTP server.
 *
 * Kenapa tidak lewat `php artisan serve`: server bawaan PHP hanya satu proses,
 * jadi hasilnya mengukur server itu, bukan biaya aplikasi. Di sini controller
 * dipanggil langsung N kali, bergantian antara engine php dan go.
 *
 * Jalankan dari root project (service Go harus hidup di 127.0.0.1:8081):
 *   php go-umkm/parity/bench_landing.php 60 php,go
 */

ini_set('display_errors', 'stderr');

require __DIR__.'/../../vendor/autoload.php';

$app = require __DIR__.'/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\DataUmkmController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

$iterasi = (int) ($argv[1] ?? 50);
$modeList = explode(',', $argv[2] ?? 'php,go');

// Sesi tamu dengan preferensi eksplisit, sama seperti user yang baru memilih
// kategori di modal. Dibuat sekali, dipakai semua iterasi.
$session = $app['session']->driver();
$session->setId('bench-sesi-'.substr(md5((string) microtime(true)), 0, 20));
$session->start();
$session->put('umkm_preferred_categories', [1, 7]);
$session->put('umkm_preference_prompted', true);

$controller = new DataUmkmController;

foreach ($modeList as $mode) {
    $mode = trim($mode);
    config(['services.rekomendasi.engine' => $mode]);
    Cache::forget('rekomendasi:service-mati');
    $session->put('umkm_preference_prompted', true);

    // pemanasan
    for ($i = 0; $i < 3; $i++) {
        $req = Request::create('/?page=1', 'GET');
        $req->setLaravelSession($session);
        $app->instance('request', $req);
        $app['auth']->forgetGuards();
        $controller->landing($req);
    }

    $durasi = [];
    $memori = [];
    for ($i = 0; $i < $iterasi; $i++) {
        // seed acak baru tiap iterasi supaya shuffle tidak memakai jalur cache
        $req = Request::create('/?page=1&iterasi='.$i, 'GET');
        $req->setLaravelSession($session);
        $app->instance('request', $req);
        $app['auth']->forgetGuards();

        gc_collect_cycles();
        $t0 = microtime(true);
        $controller->landing($req);
        $durasi[] = (microtime(true) - $t0) * 1000;
        $memori[] = memory_get_peak_usage(true) / 1048576;
    }

    sort($durasi);
    $ambil = fn (float $p) => $durasi[(int) ((count($durasi) - 1) * $p)];
    $rata = array_sum($durasi) / count($durasi);

    printf(
        "%-4s n=%d  rata=%6.2f ms  p50=%6.2f  p95=%6.2f  p99=%6.2f  max=%6.2f  peak_mem=%4.1f MB\n",
        $mode,
        count($durasi),
        $rata,
        $ambil(0.50),
        $ambil(0.95),
        $ambil(0.99),
        $durasi[count($durasi) - 1],
        max($memori),
    );
}

// Bersihkan sesi benchmark supaya tidak menumpuk di tabel sessions.
$session->flush();
