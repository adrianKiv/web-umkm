// Package repo berisi query baca-saja ke database UMKM.
// Semua query di sini sengaja agregat: satu round-trip ke MySQL, bukan
// pola N+1 seperti `$umkm->rating->avg()` di Eloquent.
package repo

import (
	"context"
	"database/sql"
	"time"

	"umkm/rekomendasi/internal/cache"
	"umkm/rekomendasi/internal/cbf"
)

// TTLDefault = umur cache data yang jarang berubah. 60 detik artinya paling
// lama data admin terlihat "telat" satu menit; cukup untuk landing page.
const TTLDefault = 60 * time.Second

// Store membungkus *sql.DB supaya handler tidak pegang SQL langsung.
type Store struct {
	db   *sql.DB
	peta *cache.TTL[KategoriKelompok]
	umkm *cache.TTL[[]cbf.Umkm]
}

func New(db *sql.DB) *Store {
	s := &Store{db: db}
	s.SetCacheTTL(TTLDefault)
	return s
}

// SetCacheTTL memasang umur cache baru (0 = mati, tiap request query DB).
// Dipakai dari env supaya bisa dibandingkan saat benchmark.
func (s *Store) SetCacheTTL(ttl time.Duration) {
	s.peta = cache.New[KategoriKelompok](ttl)
	s.umkm = cache.New[[]cbf.Umkm](ttl)
}

// InvalidateCaches memaksa ambil ulang dari DB. Dipanggil manual setelah
// admin mengubah kategori/rating kalau tidak mau menunggu TTL.
func (s *Store) InvalidateCaches() {
	s.peta.Reset()
	s.umkm.Reset()
}

// Ping dipakai health check: jangan bilang "ok" kalau DB sudah mati.
func (s *Store) Ping(ctx context.Context) error {
	return s.db.PingContext(ctx)
}

// KategoriStat = satu baris tabel kategori + statistik UMKM dan ratingnya.
type KategoriStat struct {
	ID           int
	Nama         string
	Kelompok     string
	JumlahUmkm   int
	JumlahRating int
	RatingRata   float64
}

// PerKategori: agregasi dihitung per tabel dulu (derived table) lalu di-join.
// Versi "JOIN fan-out" yang lebih intuitif 4x lebih lambat di mesin ini
// (45ms vs 10ms) karena MySQL membuat 10x14x28 baris lalu filesort.
// Angka yang dihasilkan identik: sudah dibandingkan dengan query JOIN.
func (s *Store) PerKategori(ctx context.Context) ([]KategoriStat, error) {
	const query = `
		SELECT k.id_kategori,
		       k.nama_kategori,
		       kl.nama_kelompok,
		       COALESCE(u.jumlah, 0)          AS jumlah_umkm,
		       COALESCE(r.jumlah, 0)          AS jumlah_rating,
		       COALESCE(r.rata, 0)            AS rata_rating
		FROM kategori k
		JOIN kelompok kl ON kl.id_kelompok = k.id_kelompok
		LEFT JOIN (
			SELECT id_kategori, COUNT(*) AS jumlah
			FROM umkm
			GROUP BY id_kategori
		) u ON u.id_kategori = k.id_kategori
		LEFT JOIN (
			SELECT u2.id_kategori, COUNT(*) AS jumlah, AVG(r2.nilai_rating) AS rata
			FROM rating r2
			JOIN umkm u2 ON u2.id_umkm = r2.id_umkm
			GROUP BY u2.id_kategori
		) r ON r.id_kategori = k.id_kategori
		ORDER BY jumlah_umkm DESC, k.id_kategori ASC`

	rows, err := s.db.QueryContext(ctx, query)
	if err != nil {
		return nil, err
	}
	defer rows.Close()

	var out []KategoriStat
	for rows.Next() {
		var k KategoriStat
		if err := rows.Scan(&k.ID, &k.Nama, &k.Kelompok, &k.JumlahUmkm, &k.JumlahRating, &k.RatingRata); err != nil {
			return nil, err
		}
		out = append(out, k)
	}

	return out, rows.Err()
}

// UmkmKlik = baris leaderboard.
type UmkmKlik struct {
	ID        int
	Nama      string
	Kategori  string
	TotalKlik int64
}

// TopKlik: total_klik bisa NULL (kolom ini ditambahkan manual di production,
// bukan lewat migration), jadi wajib COALESCE.
func (s *Store) TopKlik(ctx context.Context, limit int) ([]UmkmKlik, error) {
	const query = `
		SELECT u.id_umkm, u.nama_umkm, k.nama_kategori, COALESCE(u.total_klik, 0)
		FROM umkm u
		JOIN kategori k ON k.id_kategori = u.id_kategori
		ORDER BY COALESCE(u.total_klik, 0) DESC, u.id_umkm ASC
		LIMIT ?`

	rows, err := s.db.QueryContext(ctx, query, limit)
	if err != nil {
		return nil, err
	}
	defer rows.Close()

	var out []UmkmKlik
	for rows.Next() {
		var u UmkmKlik
		if err := rows.Scan(&u.ID, &u.Nama, &u.Kategori, &u.TotalKlik); err != nil {
			return nil, err
		}
		out = append(out, u)
	}

	return out, rows.Err()
}

// Ringkasan = angka global + kelengkapan data. Bagian kelengkapan penting
// untuk skripsi: rekomendasi CBF kualitasnya dibatasi data yang ada.
type Ringkasan struct {
	Umkm         int
	Kategori     int
	Kelompok     int
	Rating       int
	Aktivitas    int
	TanpaRating  int
	TanpaMenu    int
	TanpaLokasi  int
	RataKlikUmkm float64
}

func (s *Store) Ringkasan(ctx context.Context) (Ringkasan, error) {
	const query = `
		SELECT
			(SELECT COUNT(*) FROM umkm),
			(SELECT COUNT(*) FROM kategori),
			(SELECT COUNT(*) FROM kelompok),
			(SELECT COUNT(*) FROM rating),
			(SELECT COUNT(*) FROM user_activities),
			(SELECT COUNT(*) FROM umkm u WHERE NOT EXISTS (SELECT 1 FROM rating r WHERE r.id_umkm = u.id_umkm)),
			(SELECT COUNT(*) FROM umkm u WHERE NOT EXISTS (SELECT 1 FROM menu m WHERE m.id_umkm = u.id_umkm)),
			(SELECT COUNT(*) FROM umkm u WHERE NOT EXISTS (SELECT 1 FROM lokasi l WHERE l.id_lokasi = u.id_lokasi)),
			(SELECT COALESCE(AVG(COALESCE(total_klik, 0)), 0) FROM umkm)`

	var r Ringkasan
	err := s.db.QueryRowContext(ctx, query).Scan(
		&r.Umkm, &r.Kategori, &r.Kelompok, &r.Rating, &r.Aktivitas,
		&r.TanpaRating, &r.TanpaMenu, &r.TanpaLokasi, &r.RataKlikUmkm,
	)
	return r, err
}
