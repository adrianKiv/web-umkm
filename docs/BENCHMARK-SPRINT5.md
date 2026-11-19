# Benchmark Sprint 5 — bukti kuantitatif

Semua angka di bawah ini dihasilkan dari eksekusi nyata di mesin ini, bukan perkiraan.
File mentah latensi per request ada di `go-umkm/bench_*.txt` (di-gitignore).

## Metodologi

| Item | Nilai |
|---|---|
| CPU / OS | Intel i7-9750HF, Windows 11 |
| Go | go1.27.4 windows/amd64 |
| PHP | 8.4.20 (Laragon), MySQL 8.0.30 lokal |
| Data | 147 UMKM, 4.224 rating, 861 aktivitas, 10 kategori, 3 kelompok |
| Alat beban | `go-umkm/cmd/loadtest` (Go, keep-alive, 20 request pemanasan) |
| Jumlah sampel | 500 request per konfigurasi (300 untuk cache-off), 3000 untuk uji memori |
| Persentil | dari latensi per-request yang diurutkan, bukan rata-rata |
| Cache service | `REKOMENDASI_CACHE_TTL=60` (default) atau `0` (mati) |
| Klien | satu host, loopback, tanpa TLS — angka relatif, bukan SLA produksi |

Dua hal yang **tidak** dilakukan, dan alasannya:
1. Tidak memakai `php artisan serve` untuk uji beban: server bawaan PHP hanya satu proses, jadi yang terukur adalah server itu, bukan biaya aplikasi.
2. Tidak memakai `curl` per request: tiap pemanggilan `curl` menambah ~20 ms startup proses (ini yang sempat membuat angka Sprint 2 salah).

## 1. Service Go — latensi & throughput

500 request, 0 error di semua baris.

| Konfigurasi | p50 | p95 | p99 | max | req/detik (sukses) |
|---|---|---|---|---|---|
| cache ON, c=1 | 540 µs | 610 µs | 1,07 ms | 2,59 ms | 2.586 |
| cache ON, c=10 | 900 µs | 2,76 ms | 5,37 ms | 6,34 ms | 9.231 |
| cache ON, c=50 | 2,82 ms | 17,62 ms | 30,6 ms | 39,26 ms | 8.212 |
| cache ON, c=10, tamu + sesi nyata | 5,11 ms | 10,55 ms | 12,11 ms | 18,92 ms | 1.718 |
| cache ON, c=20, n=3000 (uji memori) | 2,09 ms | 8,28 ms | 13,67 ms | 28,71 ms | 6.478 |

Baca:
- Tanpa query DB per request (data ter-cache), service mentok di ~9-11 ribu req/detik pada CPU ini.
- c=50 lebih lambat dari c=10 → saturasi, bukan skala linear. Penyebab yang terukur: pool MySQL 10 koneksi + satu query per request pada jalur sesi.
- Jalur tamu ber-sesi 5x lebih lambat daripada jalur tanpa sesi, karena tiap request tetap menembak `user_activities` (data klik berubah terus, tidak boleh di-cache).

## 2. Efek cache (variabel paling berpengaruh)

| Konfigurasi | p50 | req/detik | Selisih |
|---|---|---|---|
| cache ON, c=1 | 540 µs | 2.586 | baseline |
| cache OFF, c=1 | 16,48 ms | 58 | **30x lebih lambat** |
| cache ON, c=10 | 900 µs | 9.231 | baseline |
| cache OFF, c=10 | 63,02 ms | 150 | **61x lebih rendah** |

## 3. Efek index `user_activities`

Kondisi: jalur tamu + sesi, c=10, 500 request.

| Kondisi | p50 | p95 | req/detik |
|---|---|---|---|
| tanpa index | 5,11 ms | 10,55 ms | 1.718 |
| dengan index `(interaction_type, id_session, id_kategori)` | 3,31 ms | 6,89 ms | 2.604 |

Index **dipasang lalu dihapus lagi** setelah pengukuran; DB kembali seperti semula (diverifikasi `SHOW INDEX`).
Kesimpulan: +52% throughput di jalur sesi. Untuk 795 baris ini masih opsional; akan makin penting saat tabel klik tumbuh.

## 4. Halaman landing: engine PHP vs engine Go

`DataUmkmController::landing()` dipanggil langsung 50x per mode (tanpa HTTP server), sesi sama, preferensi kategori [1,7], Blade sudah hangat.

| Mode | rata-rata | p50 | p95 | p99 | peak memori PHP |
|---|---|---|---|---|---|
| `REKOMENDASI_ENGINE=php` | 170,45 ms | **163,74 ms** | 198,40 ms | 220,00 ms | 32 MB |
| `REKOMENDASI_ENGINE=go` | 157,79 ms | **145,65 ms** | 201,03 ms | 252,83 ms | 34 MB |

Selisih p50: **-18,1 ms (-11%)**.

Diulang dengan 70 iterasi per mode (run kedua, sesi baru):

| Mode | rata-rata | p50 | p95 | p99 | max |
|---|---|---|---|---|---|
| `php` | 159,66 ms | **152,90 ms** | 185,40 ms | 202,81 ms | 206,55 ms |
| `go` | 145,16 ms | **135,47 ms** | 162,97 ms | 184,74 ms | 460,05 ms |

Selisih p50: **-17,4 ms (-11,4%)** — arah dan besarnya konsisten. Median stabil; ekor (p99/max) berisik, satu outlier 460 ms muncul di mode go (kemungkinan GC/IO), jadi jangan klaim perbaikan tail.

### Kenapa cuma 11% — ini temuan utama
Diukur terpisah, biaya "muat 147 UMKM + relasi + hitung skor" di PHP = **~52 ms per permintaan** (selisih 1 vs 21 skenario pada `cbf_reference.php`, setelah dikurangi boot 1,22 s). Service Go mengerjakan bagian itu dalam **~1,5 ms** (cache hangat) — 35x lebih cepat.

Tapi `landing()` tetap harus memuat 147 UMKM beserta 4.224 rating lewat Eloquent untuk dirender Blade. Jadi:
- skor rekomendasi: 52 ms → 1,5 ms (Go)
- sisa halaman (Eloquent eager load + render + pagination + leaderboard): ~110 ms, tidak berubah

**Kesimpulan yang jujur untuk skripsi:** memindahkan algoritma CBF ke Go mempercepat bagian komputasinya 35x, tapi dampak ke halaman hanya 11% karena bottleneck sebenarnya ada di ORM/render, bukan di matematika skor. Rekomendasi lanjutan: cache halaman/partial rekomendasi, atau kurangi eager load (`rating` hanya dipakai untuk rata-rata → pindahkan agregasi ke SQL).

## 5. Memori

| Proses | Angka | Catatan |
|---|---|---|
| Service Go (biner tunggal) | **16,7 MB** working set, 52,1 MB private | stabil selama 3.000 request c=20 |
| Satu permintaan PHP | 32-34 MB peak | `memory_get_peak_usage(true)` |

Alokasi algoritma (dari `go test -bench`, 147 UMKM): **10,1 µs/op, 28 alloc/op, 20 KB/op**.

## 6. Cara mereproduksi

```bash
# 1. MySQL hidup + service Go
cd go-umkm && go build -buildvcs=false -o bin/server.exe ./cmd/server
REKOMENDASI_CACHE_TTL=60 ./bin/server.exe -addr 127.0.0.1:8081

# 2. Beban
go build -buildvcs=false -o bin/loadtest.exe ./cmd/loadtest
./bin/loadtest.exe -n 500 -c 1
./bin/loadtest.exe -n 500 -c 10
./bin/loadtest.exe -n 500 -c 50
./bin/loadtest.exe -n 500 -c 10 -body '{"explicit_categories":[],"session_id":"<sesi asli>"}'

# 3. Cache mati (banding)
REKOMENDASI_CACHE_TTL=0 ./bin/server.exe -addr 127.0.0.1:8081

# 4. Landing: PHP vs Go
cd .. && php go-umkm/parity/bench_landing.php 50 php,go

# 5. Algoritma murni
cd go-umkm && go test ./internal/cbf -run XXX -bench Skor -benchtime 2s -benchmem
```

## 7. Batasan yang wajib disebut di skripsi
1. Semua angka dari satu laptop (i7-9750HF), DB di mesin yang sama — bukan Azure, tanpa latensi jaringan.
2. PHP diuji tanpa PHP-FPM paralel; perbandingan adil untuk biaya aplikasi, tidak untuk kapasitas server produksi.
3. Skor memakai cache 60 detik; data admin bisa telat sampai 1 menit.
4. Persentil dari 500 sampel: p99 = 5 request terakhir, cukup untuk indikasi, bukan untuk SLO ketat.
5. Jalur filter aktif memakai PHP, jadi angka "engine=go" hanya berlaku untuk landing tanpa filter.
