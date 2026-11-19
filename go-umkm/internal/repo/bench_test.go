package repo

import (
	"context"
	"os"
	"testing"
	"time"

	"umkm/rekomendasi/internal/cbf"
	"umkm/rekomendasi/internal/config"
	"umkm/rekomendasi/internal/db"
)

// Benchmark butuh DB asli. Jalankan:
//
//	UMKM_DB_TEST=1 go test ./internal/repo -run XXX -bench Rekomendasi -benchmem
func buatStore(b *testing.B) *Store {
	b.Helper()

	cfg, err := config.LoadDB()
	if err != nil {
		b.Fatalf("config: %v", err)
	}
	handle, err := db.Open(context.Background(), cfg)
	if err != nil {
		b.Fatalf("koneksi: %v", err)
	}
	b.Cleanup(func() { handle.Close() })

	return New(handle)
}

func benchmarkRekomendasi(b *testing.B, ttl time.Duration) {
	if os.Getenv("UMKM_DB_TEST") == "" {
		b.Skip("set UMKM_DB_TEST=1")
	}
	store := buatStore(b)
	store.SetCacheTTL(ttl)

	ctx := context.Background()
	explicit := []int{1, 7}
	params := cbf.DefaultParams()

	// Pastikan cache terisi sebelum diukur (kalau tidak, run pertama ikut
	// menghitung query awal dan hasilnya menyesatkan).
	if _, err := store.Rekomendasi(ctx, explicit, 0, "", params); err != nil {
		b.Fatal(err)
	}

	b.ResetTimer()
	for i := 0; i < b.N; i++ {
		if _, err := store.Rekomendasi(ctx, explicit, 0, "", params); err != nil {
			b.Fatal(err)
		}
	}
}

// BenchmarkRekomendasiCache: kondisi normal service (cache 60 detik).
func BenchmarkRekomendasiCache(b *testing.B) { benchmarkRekomendasi(b, time.Minute) }

// BenchmarkRekomendasiTanpaCache: kondisi Sprint 2 (3 query tiap request).
func BenchmarkRekomendasiTanpaCache(b *testing.B) { benchmarkRekomendasi(b, 0) }

// BenchmarkImplicitCounts mengukur satu-satunya query yang tetap jalan tiap
// request (data klik per sesi memang berubah terus).
func BenchmarkImplicitCounts(b *testing.B) {
	if os.Getenv("UMKM_DB_TEST") == "" {
		b.Skip("set UMKM_DB_TEST=1")
	}
	store := buatStore(b)
	ctx := context.Background()

	b.ResetTimer()
	for i := 0; i < b.N; i++ {
		if _, err := store.ImplicitCounts(ctx, 0, "0F7ff3VI6LrXumO2EpC3vmoCwJxJVPEriAND6QYn"); err != nil {
			b.Fatal(err)
		}
	}
}

func BenchmarkUmkmRingkasCold(b *testing.B) {
	if os.Getenv("UMKM_DB_TEST") == "" {
		b.Skip("set UMKM_DB_TEST=1")
	}
	store := buatStore(b)
	ctx := context.Background()

	b.ResetTimer()
	for i := 0; i < b.N; i++ {
		if _, err := store.loadUmkmRingkas(ctx); err != nil {
			b.Fatal(err)
		}
	}
}
