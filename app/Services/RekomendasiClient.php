<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Klien HTTP ke service rekomendasi (Go).
 *
 * Aturan penting: kelas ini TIDAK BOLEH membuat landing page gagal.
 * Semua error (timeout, 5xx, JSON rusak, service mati) dikembalikan sebagai
 * null supaya controller jatuh ke algoritma PHP lama.
 */
class RekomendasiClient
{
    /** Kunci cache: penanda "service sedang mati, jangan dicoba dulu". */
    private const KUNCI_MATI = 'rekomendasi:service-mati';

    public function mode(): string
    {
        return (string) config('services.rekomendasi.engine', 'php');
    }

    /** Service dipanggil sama sekali? (mode go atau shadow) */
    public function aktif(): bool
    {
        return in_array($this->mode(), ['go', 'shadow'], true)
            && filled(config('services.rekomendasi.url'));
    }

    public function shadow(): bool
    {
        return $this->mode() === 'shadow';
    }

    /**
     * Minta rekomendasi ke service Go.
     *
     * @param  array<int,int>  $explicit  preferensi eksplisit (session/cookie)
     * @return array{top_category_ids: array<int,int>, skor_kategori: array<int,float>, id_umkm: array<int,int>, lama_ms: float}|null
     */
    public function rekomendasi(array $explicit, ?int $userId, string $sessionId): ?array
    {
        if (! $this->aktif()) {
            return null;
        }

        // Circuit breaker: tanpa ini, tiap request landing membayar ~0,5 detik
        // menunggu service yang sudah mati. Sekali gagal, istirahat sebentar.
        if (Cache::get(self::KUNCI_MATI)) {
            return null;
        }

        $mulai = microtime(true);

        try {
            $response = Http::baseUrl((string) config('services.rekomendasi.url'))
                ->withHeaders(array_filter([
                    'X-API-Key' => config('services.rekomendasi.key'),
                ]))
                ->acceptJson()
                ->asJson()
                ->connectTimeout(0.25)
                ->timeout((float) config('services.rekomendasi.timeout', 0.8))
                ->post('/api/v1/rekomendasi', [
                    'explicit_categories' => array_values(array_map('intval', $explicit)),
                    'session_id' => $sessionId,
                    'user_id' => (int) ($userId ?? 0),
                ]);
        } catch (ConnectionException $e) {
            $this->catatGagal('koneksi', $e->getMessage(), $mulai);

            return null;
        } catch (Throwable $e) {
            $this->catatGagal('exception', $e->getMessage(), $mulai);

            return null;
        }

        if (! $response->successful()) {
            $this->catatGagal('status '.$response->status(), $response->body(), $mulai);

            return null;
        }

        Cache::forget(self::KUNCI_MATI);

        $body = $response->json();
        if (! is_array($body) || ! isset($body['top_category_ids'], $body['recommended'])) {
            $this->catatGagal('json tidak sesuai', $response->body(), $mulai);

            return null;
        }

        $skor = [];
        foreach ($body['skor_kategori'] ?? [] as $baris) {
            if (isset($baris['id_kategori'])) {
                $skor[(int) $baris['id_kategori']] = (float) $baris['skor'];
            }
        }

        return [
            'top_category_ids' => array_map('intval', $body['top_category_ids']),
            'skor_kategori' => $skor,
            'id_umkm' => array_map(fn ($item) => (int) $item['id_umkm'], $body['recommended']),
            'lama_ms' => (float) ($body['lama_ms'] ?? 0),
        ];
    }

    /**
     * Shadow mode: bandingkan hasil PHP vs Go, catat hanya kalau beda.
     * Dipanggil di jalur request, jadi sengaja ringan (tanpa query tambahan).
     *
     * @param  array<int,float>  $skorLokal
     * @param  array<int,int>  $topLokal
     */
    public function bandingkan(array $skorLokal, array $topLokal, array $remote): void
    {
        $topSama = $topLokal === $remote['top_category_ids'];

        $skorBeda = [];
        foreach ($skorLokal as $id => $nilai) {
            $nilaiRemote = $remote['skor_kategori'][$id] ?? null;
            if ($nilaiRemote === null || abs($nilaiRemote - (float) $nilai) > 1e-9) {
                $skorBeda[(int) $id] = ['php' => (float) $nilai, 'go' => $nilaiRemote];
            }
        }
        foreach ($remote['skor_kategori'] as $id => $nilai) {
            if (! array_key_exists($id, $skorLokal)) {
                $skorBeda[(int) $id] = ['php' => null, 'go' => $nilai];
            }
        }

        if ($topSama && $skorBeda === []) {
            Log::debug('cbf-paritas: sama', [
                'top' => $topLokal,
                'lama_ms_go' => $remote['lama_ms'],
            ]);

            return;
        }

        Log::warning('cbf-paritas: BEDA', [
            'top_php' => $topLokal,
            'top_go' => $remote['top_category_ids'],
            'skor_beda' => $skorBeda,
        ]);
    }

    private function catatGagal(string $sebab, string $detail, float $mulai): void
    {
        $cooldown = (int) config('services.rekomendasi.cooldown', 30);
        if ($cooldown > 0) {
            Cache::put(self::KUNCI_MATI, true, $cooldown);
        }

        Log::warning('cbf-service gagal, fallback ke PHP', [
            'sebab' => $sebab,
            'detail' => mb_substr($detail, 0, 300),
            'durasi_ms' => round((microtime(true) - $mulai) * 1000, 2),
        ]);
    }
}
