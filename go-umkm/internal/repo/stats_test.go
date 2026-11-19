package repo

import (
	"context"
	"os"
	"testing"
	"time"

	"umkm/rekomendasi/internal/config"
	"umkm/rekomendasi/internal/db"
)

// TestIntegrasi butuh MySQL hidup dan .env Laravel. Di-skip by default supaya
// `go test ./...` tetap jalan di mesin tanpa DB (misal di CI).
//
//	UMKM_DB_TEST=1 go test ./internal/repo -v
func TestIntegrasi(t *testing.T) {
	if os.Getenv("UMKM_DB_TEST") == "" {
		t.Skip("set UMKM_DB_TEST=1 untuk menjalankan test integrasi")
	}

	cfg, err := config.LoadDB()
	if err != nil {
		t.Fatalf("config: %v", err)
	}

	ctx, cancel := context.WithTimeout(context.Background(), 20*time.Second)
	defer cancel()

	handle, err := db.Open(ctx, cfg)
	if err != nil {
		t.Fatalf("koneksi: %v", err)
	}
	defer handle.Close()

	store := New(handle)

	ringkasan, err := store.Ringkasan(ctx)
	if err != nil {
		t.Fatalf("ringkasan: %v", err)
	}
	if ringkasan.Umkm == 0 {
		t.Fatal("umkm kosong: DB salah atau belum di-seed")
	}

	stats, err := store.PerKategori(ctx)
	if err != nil {
		t.Fatalf("per kategori: %v", err)
	}
	if len(stats) != ringkasan.Kategori {
		t.Errorf("baris kategori %d, tapi COUNT(kategori)=%d", len(stats), ringkasan.Kategori)
	}

	// Invarian yang harus benar apa pun isi datanya: join ke rating tidak boleh
	// menggandakan UMKM, dan rata-rata rating harus dalam skala 1-5.
	total := 0
	for _, k := range stats {
		total += k.JumlahUmkm
		if k.JumlahUmkm > 0 && (k.RatingRata < 1 || k.RatingRata > 5) {
			t.Errorf("kategori %d rata rating %.2f di luar 1-5", k.ID, k.RatingRata)
		}
		if k.JumlahRating > 0 && k.RatingRata == 0 {
			t.Errorf("kategori %d punya %d rating tapi rata 0", k.ID, k.JumlahRating)
		}
	}
	if total != ringkasan.Umkm {
		t.Errorf("jumlah UMKM per kategori=%d, total umkm=%d (ada data yatim?)", total, ringkasan.Umkm)
	}

	top, err := store.TopKlik(ctx, 10)
	if err != nil {
		t.Fatalf("top klik: %v", err)
	}
	if len(top) != min(10, ringkasan.Umkm) {
		t.Errorf("leaderboard %d baris, mau %d", len(top), min(10, ringkasan.Umkm))
	}
	for i := 1; i < len(top); i++ {
		if top[i-1].TotalKlik < top[i].TotalKlik {
			t.Errorf("leaderboard tidak urut di posisi %d", i+1)
		}
	}

	t.Logf("umkm=%d kategori=%d rating=%d aktivitas=%d tanpa_rating=%d",
		ringkasan.Umkm, ringkasan.Kategori, ringkasan.Rating, ringkasan.Aktivitas, ringkasan.TanpaRating)
}
