<?php

/**
 * Membuat skenario uji paritas Sprint 2 dari data user_activities nyata.
 *
 * Jalankan dari root project:
 *   php go-umkm/parity/make_scenarios.php > go-umkm/parity/cbf_scenarios.json
 */

ini_set('display_errors', 'stderr');

require __DIR__ . '/../../vendor/autoload.php';

$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$kategoriIds = DB::table('kategori')->orderBy('id_kategori')->pluck('id_kategori')->map(fn($v) => (int) $v)->all();
$sessions = DB::select(
    "SELECT id_session, COUNT(*) c FROM user_activities
     WHERE interaction_type = 'detail_click'
     GROUP BY id_session ORDER BY c DESC LIMIT 10"
);

$scenarios = [];

// 1. Tamu dengan riwayat klik nyata (10 sesi teratas).
foreach ($sessions as $i => $row) {
    $scenarios[] = [
        'nama' => 'session-top-' . ($i + 1),
        'explicit' => [],
        'session_id' => $row->id_session,
        'user_id' => 0,
    ];
}

// 2. Sesi nyata + preferensi eksplisit (5 kombinasi berbeda).
$kombinasi = [[], [$kategoriIds[1] ?? 2], [$kategoriIds[0] ?? 1, $kategoriIds[6] ?? 7], [$kategoriIds[9] ?? 10], [$kategoriIds[2] ?? 3, $kategoriIds[4] ?? 5]];
foreach (array_slice($sessions, 0, 5) as $i => $row) {
    $scenarios[] = [
        'nama' => 'session-explicit-' . ($i + 1),
        'explicit' => $kombinasi[$i] ?? [],
        'session_id' => $row->id_session,
        'user_id' => 0,
    ];
}

// 3. Murni eksplisit tanpa riwayat (5 skenario) + satu sesi kosong.
$sintetis = [
    ['eksplisit-satu', [$kategoriIds[0] ?? 1]],
    ['eksplisit-dua-kelompok', [$kategoriIds[0] ?? 1, $kategoriIds[6] ?? 7]],
    ['eksplisit-tiga', [$kategoriIds[1] ?? 2, $kategoriIds[5] ?? 6, $kategoriIds[9] ?? 10]],
    ['eksplisit-semua', $kategoriIds],
    ['eksplisit-kosong', []],
];
foreach ($sintetis as [$nama, $ids]) {
    $scenarios[] = [
        'nama' => $nama,
        'explicit' => array_values($ids),
        'session_id' => 'sesi-tidak-ada',
        'user_id' => 0,
    ];
}

// 4. User login (kalau ada) — jalur filter id_user, bukan id_session.
$user = DB::table('user_activities')->whereNotNull('id_user')->value('id_user');
if ($user) {
    $scenarios[] = [
        'nama' => 'user-login',
        'explicit' => [],
        'session_id' => '',
        'user_id' => (int) $user,
    ];
}

echo json_encode($scenarios, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), "\n";
