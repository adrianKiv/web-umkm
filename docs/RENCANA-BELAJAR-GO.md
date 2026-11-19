# Rencana Belajar Go + Terap di UMKM-web

Konteks project (hasil audit `D:\kuliah\semester 8 final act\UMKM-web`):

| Aspek | Kondisi |
|---|---|
| Framework | Laravel 12, PHP 8.2+ (CI pakai 8.4) |
| DB | MySQL, `DB_DATABASE=cobaleaflet` |
| Deploy | Azure Web App `umkmupi` (code deploy via GitHub Actions `main_umkmupi.yml`, slot Production) |
| Inti skripsi | CBF recommendation di `DataUmkmController::landing()` (L74-172): explicit score 1.0, implicit weight 0.10, sibling spillover 0.50, alokasi 4-3-3, evaluasi di `routes/web.php` L22+ (270 run) |
| Hot path lain | `mapData()` bbox query, `SyncOsmUmkm` command, leaderboard `total_klik` |
| Ukuran | `app/` 3.927 LOC, `routes/web.php` 2.051 LOC |
| Go di mesin | belum terinstall (`go: command not found`) |

## Keputusan arsitektur (kenapa ini, bukan monolith rewrite)

1. **Go = service tambahan, bukan pengganti Laravel.** Auth, session, Blade, admin CRUD tetap PHP. Go tidak menyentuh session → stateless, gampang di-test, gampang di-rollback.
2. **Satu tanggung jawab: scoring/ranking CBF.** Ini bagian yang paling mahal secara komputasi (sekarang: semua UMKM di-load, `rating->avg()` per item, spillover loop di PHP) dan paling mudah dibuktikan lebih cepat.
3. **Feature flag + shadow mode.** `REKOMENDASI_ENGINE=php|go`. Kalau Go mati → fallback PHP. Sidang aman: production tidak pernah bergantung pada kode baru.
4. **Tidak ubah skema DB.** Go read-only ke MySQL yang sama. Satu-satunya perubahan DB: index untuk query implicit (opsional, aman).

Kenapa BUKAN yang lain (untuk fase belajar ini):
- Ganti seluruh Laravel → risiko skripsi gagal, tidak selesai dalam waktu wajar.
- Auth di Go → butuh share session/cookie Laravel, banyak jebakan, nilai belajar rendah.
- Realtime websocket → tidak ada kebutuhan di project.

## Peta konsep: PHP/Laravel → Go

| Kamu sudah tahu | Di Go jadi | Catatan |
|---|---|---|
| `composer.json` | `go.mod` | dependency sedikit, banyak stdlib |
| Controller | `http.HandlerFunc` | tidak ada magic, routing manual/`chi` |
| Eloquent | `database/sql` + struct scan | eksplisit, tidak ada lazy loading |
| `try/catch` | `if err != nil` | error = nilai, bukan exception |
| Service container | konstruktor manual / wire | DI eksplisit |
| Artisan command | `main.go` + flag / cron | satu binary, tanpa runtime |
| PHP-FPM worker | goroutine | concurrency murah, tapi harus dijaga |
| Pest/PHPUnit | `testing` bawaan + table test | `go test ./...` |

## Sprint plan (asumsi 8-10 jam/minggu, total ±7 minggu)

### Sprint 0 — Setup + warm-up ✅ SELESAI (15 Nov 2026)
- Go 1.27.4 terpasang (`go version`), module `go-umkm` (`umkm/rekomendasi`).
- **Artefak nyata:** `internal/kategori/kategori.go` (port `getKategoriId`), `internal/kategori/slug.go` (port `Str::slug` + osm_id), `cmd/warmup`, `cmd/parity`, `parity/php_fixture.php`, `parity/golden_php.json`, `.gitignore`.
- **Bukti paritas:** `parity/run.sh` → PHP asli (via `vendor/autoload.php`, Str sungguhan) vs Go = **14/14 identik**. Dikunci sebagai test: `go test ./...` → `TestParityDenganPHP PASS`.
- **Temuan bug production:** `"Café Sudirman"` tanpa tag `amenity` → kategori 1 (Nasi & Warteg), karena PHP hanya cek kata `kopi`, bukan `cafe`. Kandidat temuan untuk bab pembahasan (jangan diperbaiki sebelum paritas selesai; catat dulu).

```bash
cd "D:/kuliah/semester 8 final act/UMKM-web/go-umkm"
bash parity/run.sh          # PHP vs Go, harus IDENTIK
go test ./...               # paritas terkunci sebagai regression test
go run ./cmd/warmup         # tabel 10 sampel
```

**DoD tercapai:** mapping kategori + slug identik dengan PHP untuk 14 sampel, terverifikasi terhadap kode PHP asli, bukan asumsi.

### Sprint 1 — Go dasar lewat data nyata ✅ SELESAI (15 Nov 2026)
- Konsep terpakai: package, struct, method, `context` + timeout, `database/sql` pool, error return, generics ringan (`timed[T]`), `text/tabwriter`.
- **Artefak:** `internal/config/config.go` (parser .env + env menang), `internal/db/mysql.go` (pool 10/5, ping 5s), `internal/repo/stats.go` (3 query agregat), `cmd/stats`, `internal/repo/stats_test.go` (integrasi, gated `UMKM_DB_TEST=1`).
- **Terverifikasi terhadap DB hidup** (`cobaleaflet`): umkm=147, kategori=10, kelompok=3, rating=4224, aktivitas=861, tanpa rating=3, tanpa menu=11, rata klik=13.4. Semua angka dicocokkan ulang lewat PHP/Laravel — identik.
- **Optimasi nyata (bukan opini):** query sebaran per kategori versi JOIN fan-out = 45,0 ms; versi derived pre-aggregate = 10,3 ms (diukur 5x, query sama lewat Laravel). Di Go hasil akhir 8,7 ms. Angka keluaran identik.
- **DoD tercapai:** `go vet` bersih, `UMKM_DB_TEST=1 go test ./...` PASS (invarian: Σ umkm per kategori == total umkm, rata rating 1-5, leaderboard urut).

**Temuan data/skema (penting untuk Sprint 2):**
1. `OsmController` sudah mati: kolom `osm_id` **tidak ada** di tabel `umkm` production, jadi method itu akan error kalau dipanggil. Jangan dipakai sebagai acuan.
2. Skema live ≠ migration: `umkm.source` dan `umkm.total_klik` ada di DB tapi tidak ada migration-nya (ditambah manual). `total_klik` nullable → selalu `COALESCE`.
3. Tabel `kategori` berisi **10** kategori bernama (Nasi & Warteg, Kopi & Cafe, Martabak/Kue/Roti, Western & Fastfood, dst) — bukan ID hardcoded 1/2/3/4/7 ala OsmController. CBF di Sprint 2 **wajib** ambil mapping dari tabel, bukan konstanta.
4. `umkm`→`kategori` relasinya `hasOne` di model, padahal seharusnya `belongsTo` (kategori tidak punya id_umkm). Tidak berdampak selama hanya dipakai untuk eager load, tapi salah secara konsep.
5. 3 UMKM tanpa rating + 11 tanpa menu = batas atas kualitas rekomendasi; layak ditulis di bab pengujian.

**Catatan lingkungan:** MySQL Laragon tadinya mati; saya nyalakan (`mysqld --defaults-file=...my.ini`, datadir `C:/laragon/data/mysql-8`) supaya Sprint 1 bisa diverifikasi ke data asli. Matikan lewat Laragon kalau sudah tidak dipakai.

### Sprint 2 — HTTP service + paritas algoritma ✅ SELESAI (15 Nov 2026)
- **Artefak:** `internal/cbf/score.go` (port blok landing() 98-172), `internal/repo/rekomendasi.go` (peta kategori, umkm+rating 1 query, implicit counts), `internal/httpapi/server.go`, `cmd/server`, `parity/cbf_reference.php` (salinan verbatim landing()), `parity/make_scenarios.php`, `parity/run_cbf.sh`.
- **Endpoint:** `GET /healthz`, `POST /api/v1/rekomendasi` (header `X-API-Key` wajib kalau key di-set; tanpa key server menolak bind non-loopback). Body `{explicit_categories, session_id, user_id}` → `{top_category_ids, skor_kategori, recommended[10], diagnostics, lama_ms}`.
- **Paritas: 21/21 skenario IDENTIK** dengan PHP (`top_category_ids` + urutan + peringkat tiap item). Skenario digenerate dari `user_activities` nyata (10 sesi teratas, 5 sesi+eksplisit, 5 sintetis, 1 user login). Dicek juga lewat HTTP sungguhan: `curl` vs PHP → 10 id identik berurutan.
- **Unit test:** 6 test murni (eksplisit, implisit+spillover, akumulasi, kategori tanpa kelompok, kosong, tie-break rating) — semua PASS.
- **Temuan penting #1 — tie-break tidak deterministik:** 2 dari 21 skenario awalnya beda (`[10 1 2]` vs `[10 2 1]`, `[1 2 3]` vs `[1 2 10]`) padahal skornya seri. Penyebab: PHP memakai urutan baris hasil `GROUP BY id_kategori` (tanpa ORDER BY) sebagai penentu seri. Solusi yang dipakai: Go mempertahankan urutan baris MySQL, bukan mengurutkan sendiri. **Catatan skripsi:** perilaku ini rapuh; kalau nanti mau deterministik, tambahkan `ORDER BY id_kategori` di kedua sisi (mengubah perilaku production → butuh persetujuan dulu).
- **Temuan penting #2:** `12 × 0.1 = 1.2000000000000002` di PHP dan Go (IEEE-754). Bukan bug, tapi test harus pakai toleransi 1e-9, bukan `==`.
- **Catatan desain:** `parity/cbf_reference.php` adalah salinan kode controller. Kalau `landing()` diubah dan salinan tidak, test paritas akan menangkap perbedaannya — itu memang tujuannya.
- **Performa kasar (bukan benchmark resmi, itu Sprint 5):** 20 request berurutan ke service → p50 25 ms, p95 88 ms. Tiap request masih 3 query MySQL; kandidat optimasi = cache peta kategori + agregat rating (berubah jarang).

### Sprint 3 — Cache + benchmark ✅ SELESAI (16 Nov 2026)
- **Cakupan bergeser** dari rencana awal: pool, query agregat, dan pembunuhan N+1 sudah selesai di Sprint 1-2 (buktinya query 1 round-trip, bukan 147). Yang tersisa dan berdampak nyata = 3 query per request.
- **Artefak:** `internal/cache/ttl.go` (TTL cache generik, anti-stampede, tanpa dependency), 6 test (termasuk 50 goroutine → loader 1x), `repo.SetCacheTTL` + `repo.InvalidateCaches`, env `REKOMENDASI_CACHE_TTL` (detik; 0 = mati), `bench_test.go` di `cbf` dan `repo`.
- **Hasil benchmark (`go test -bench`, i7-9750HF, 147 UMKM / 4.224 rating):**

| Ukuran | Hasil |
|---|---|
| `Skor` algoritma murni | 10,1 µs/op, 28 alloc |
| `Rekomendasi` cache ON | 23,0 µs/op |
| `Rekomendasi` cache OFF | 11,8 ms/op (514x lebih lambat) |
| `ImplicitCounts` (1 query) | 0,96 ms/op |

- **End-to-end HTTP, klien python, 30 request:**
  - cache 60s: **p50 0,77 ms** (p95 14,8 ms — lonjakan pertama/GC)
  - cache off: p50 11,3 ms
  - jalur tamu + sesi nyata: p50 **2,45 ms** (0,96 ms di antaranya query `user_activities`)
- **Catatan metodologi:** pengukuran lama (25 ms) salah alat — tiap request `curl` bayar ~20 ms startup proses. Ukur dari dalam klien yang sudah hidup, atau pakai field `lama_ms` dari server.
- **Temuan index:** `user_activities` cuma punya PRIMARY KEY → `type=ALL rows=795 Using temporary`. Uji tambah index `(interaction_type, id_session, id_kategori)`: 0,94 ms → 0,79 ms (16%), lalu **index dihapus lagi**, DB kembali seperti semula. Kesimpulan jujur: belum layak diubah di production sekarang, tapi akan jadi penting saat tabel klik tumbuh (full scan + temporary sort naik linear).
- **DoD:** paritas 21/21 tetap PASS dengan cache aktif (cache tidak mengubah hasil), `go vet` bersih, seluruh test hijau.
- **Keterbatasan yang harus ditulis:** data admin telat maksimal 1 TTL; tidak ada invalidasi otomatis — panggil `InvalidateCaches()` setelah edit kategori/rating kalau perlu instan.

### Sprint 4 — Integrasi ke Laravel ✅ SELESAI (16 Nov 2026)
- **Artefak Laravel:** `app/Services/RekomendasiClient.php`, blok `rekomendasi` di `config/services.php`, cabang di `DataUmkmController::landing()` (algoritma lama dipindah utuh ke closure `$hitungLokal`), kunci `.env`/`.env.example`, `go-umkm/parity/verify_client.php` (verifikasi klien tanpa test suite).
- **Tiga mode:** `REKOMENDASI_ENGINE=php` (default, 0 panggilan), `shadow` (hitung keduanya, pakai PHP, selisih dicatat), `go` (pakai Go, fallback PHP).
- **Bukti A/B halaman nyata** (`php artisan serve`, sesi memilih kategori 1 & 7, MySQL asli):
  - mode php → **0** request ke service Go, 10 kartu rekomendasi
  - mode go → **1** request per page load, **10 kartu identik & urutan sama**
  - mode shadow → log `cbf-paritas: sama {"top":[1,7,2],"lama_ms_go":1.52}`, halaman tetap memakai hasil PHP
  - latensi service (cache hangat): 1,1-1,5 ms; cold 12,2 ms
- **Fallback diuji 4 kondisi:** engine php → null tanpa HTTP; service 200 → map benar (`top`, `id_umkm`, `skor`); service 500 → null; service mati → null. Semua tercatat `cbf-service gagal, fallback ke PHP`.
- **Circuit breaker (temuan dari pengukuran):** service mati membuat tiap request landing menunggu ~400-550 ms sebelum fallback. Ditambahkan penanda di cache (`rekomendasi:service-mati`, cooldown 30 s) + `connectTimeout` 0,25 s → request ke-2 dan seterusnya 27-67 ms (itu pun dominan boot PHP, bukan nol).
- **Rekayasa detail:** hasil Go dihidrasi dari `$allUmkms` yang sudah eager-loaded (`keyBy('id_umkm')`), jadi tidak ada query tambahan dan relasi (`kategori`, `rating`, `lokasi`) tetap ada untuk view. Filter aktif (`id_kategori`, `id_kelompok`, `search`, `min_rating`) **melewati Go** dan memakai PHP, karena filter tidak dikirim ke service.
- **Rollback:** ubah `REKOMENDASI_ENGINE=php` (atau hapus barisnya) — tanpa deploy ulang service, tanpa migrasi DB, tanpa perubahan data.
- **Blocker yang ditemukan (pre-existing, bukan efek sprint ini):** test suite Laravel proyek belum pernah bisa jalan di mesin ini — (a) `pdo_sqlite` tidak aktif di PHP 8.4 Laragon, (b) setelah diaktifkan, migration `->primary()->autoIncrement()` menghasilkan `table "kelompok" has more than one primary key` di SQLite. Karena itu unit test Pest untuk klien dihapus dan diganti skrip verifikasi langsung; kalau mau suite hijau, perlu perbaikan migration terpisah (atau test pakai MySQL, bukan sqlite `:memory:`).

### Sprint 5 — Bukti kuantitatif untuk skripsi ✅ SELESAI (19 Nov 2026)
- **Artefak:** `go-umkm/cmd/loadtest` (alat beban Go: p50/p95/p99 + req/detik + hitung error), `go-umkm/parity/bench_landing.php` (controller dipanggil langsung, tanpa HTTP server), `docs/BENCHMARK-SPRINT5.md` (laporan lengkap + metodologi + batasan), file mentah `go-umkm/bench_*.txt`.
- **Angka kunci** (147 UMKM, 4.224 rating, 500 request per konfigurasi, 0 error):
  - service cache ON: c=1 p50 540 µs / 2.586 rps; c=10 p50 900 µs / 9.231 rps; c=50 p50 2,82 ms (saturasi pool)
  - cache OFF: c=1 p50 16,48 ms / 58 rps → **30x lebih lambat**; c=10 63,02 ms / 150 rps → **61x throughput lebih rendah**
  - jalur tamu + sesi: p50 5,11 ms / 1.718 rps; dengan index `user_activities` sementara: 3,31 ms / 2.604 rps (**+52%**), index lalu dihapus lagi
  - landing: php p50 163,74 ms → go p50 145,65 ms (**-11%**), diulang 70 iterasi: 152,90 → 135,47 ms (**-11,4%**, konsisten)
  - memori: Go 16,7 MB working set / 52,1 MB private (stabil 3.000 request c=20); PHP 32-34 MB peak per request; algoritma 10,1 µs/op, 28 alloc
- **Temuan utama (bahan pembahasan skripsi):** biaya "muat data + hitung skor" di PHP ~52 ms, di Go ~1,5 ms (35x), tapi halaman hanya naik 11% karena sisa ~110 ms adalah eager loading Eloquent (4.224 rating) + render Blade. Jadi bottleneck sebenarnya ORM/render, bukan matematika CBF. Rekomendasi lanjutan: pindahkan agregasi rating ke SQL, cache partial rekomendasi.
- **Koreksi alat ukur:** `curl` per request menambah ~20 ms startup (angka 25 ms di Sprint 2 salah) → diganti alat beban Go; `php artisan serve` juga tidak dipakai untuk uji beban karena satu proses.
- **Insiden saat pengukuran:** MySQL sempat mati di tengah sesi; `loadtest` awalnya melaporkan "status=500x0" yang berarti *semua request gagal*, bukan 500 sukses. Alat sudah diperbaiki: kini mencetak `ok=/err=` dan keluar dengan kode 2 kalau semua gagal. Angka di laporan hanya dari run dengan `ok=500 err=0`.

### Sprint 6 — Deploy terpisah dari production Laravel (8-10 jam)

> **Penting (baca sebelum eksekusi):** App Service `umkmupi` men-deploy **seluruh isi repo** tiap push ke `main` (`webapps-deploy`, `package: .`). Artinya: jangan taruh binary/config Go yang tidak perlu di root repo, dan jangan pernah mengubah job deploy Laravel yang sudah jalan. Service Go harus jadi **App Service / Container App terpisah**, bukan sidecar di `umkmupi`.

- Dockerfile multi-stage (`golang:1.2x` builder → `distroless`/`alpine` runtime, binary statis, non-root user).
- GitHub Actions job baru `deploy-go.yml`: build image → push GHCR → deploy ke Azure Container App (`umkm-go`, internal ingress, min replicas 0/1) atau App Service Linux container baru.
- Secrets: `REKOMENDASI_API_KEY` di App Service config + key sama di container app. Jangan commit.
- **DoD:** health check `GET /healthz` dari Laravel staging balas 200; flag on di staging 1 hari tanpa error; rollback = set flag `php` (tanpa redeploy Go).

### Sprint 7 — Concurrency showcase (opsional, 8 jam)
- Pilih satu: (a) `SyncOsmUmkm` versi Go dengan worker pool + rate limit + upsert idempoten `osm_id`, atau (b) in-memory grid spatial index untuk `mapData` bbox.
- Konsep: goroutine, channel, `errgroup`, `sync.Mutex`, `context` cancel, rate limiter `golang.org/x/time/rate`.
- **DoD:** (a) sync 1.000 elemen Overpass 5x lebih cepat dari versi PHP, tanpa duplikat; jalankan **hanya di DB staging**.

## Dua pilihan tempo

| | Santai | Kilat |
|---|---|---|
| Jam/minggu | 8-10 | 15-20 |
| Durasi sampai Sprint 6 | 7 minggu | 4 minggu |
| Cocok kalau | sidang masih > 2 bulan | sidang < 1 bulan |
| Risiko | momentum turun | burnout, sprint 3-4 dangkal |

Rekomendasi: **Sprint 0-2 wajib urut** (fondasi + paritas). Sprint 3-7 boleh dipotong kalau waktu mepet; minimal sampai Sprint 4 (integrasi + flag) agar ada cerita teknis utuh.

## Definition of Done global (jangan dilanggar)
1. Kode PHP lama **tidak dihapus** sampai Go terbukti paritas di production ≥ 1 minggu.
2. Semua endpoint Go punya timeout + API key; tidak ada port publik tanpa auth.
3. Go tidak menulis ke tabel manapun di fase 1-4 (read-only). Fase 7 tulis hanya staging.
4. Setiap sprint ada artefak yang bisa di-`go run`/`go test`, bukan cuma catatan.
5. Setiap push ke `main` = deploy production. Kerja Go di branch `feat/go-service`, merge hanya setelah flag off + test hijau.

## Sumber (pilih, jangan semua)
- A Tour of Go — dasar wajib, 3-4 jam.
- Go by Example — referensi cepat per topik.
- *Let's Go* / *Let's Go Further* (Alex Edwards) — pola HTTP service + deployment produksi, paling dekat dengan kebutuhan ini.
- Effective Go — dibaca setelah Sprint 2, bukan sebelumnya.
- Dokumentasi `database/sql` + `net/http` — rujukan harian.

## Langkah pertama hari ini
1. `winget install GoLang.Go` lalu `go version`.
2. `mkdir go-umkm && cd go-umkm && go mod init umkm/rekomendasi`.
3. Port `getKategoriId()` (Sprint 0). 30 menit, tidak butuh DB, langsung ada output.
