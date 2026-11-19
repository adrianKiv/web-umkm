// Command stats = artefak Sprint 1.
// Membaca database UMKM (read-only) dan mencetak statistik yang nanti dipakai
// service rekomendasi. Semua angka diambil langsung dari MySQL, bukan tebakan.
//
// Jalankan dari folder go-umkm:
//
//	go run ./cmd/stats
//	go run ./cmd/stats -limit 15
package main

import (
	"context"
	"flag"
	"fmt"
	"os"
	"text/tabwriter"
	"time"

	"umkm/rekomendasi/internal/config"
	"umkm/rekomendasi/internal/db"
	"umkm/rekomendasi/internal/repo"
)

func main() {
	limit := flag.Int("limit", 10, "jumlah baris leaderboard klik")
	dsnOnly := flag.Bool("dsn", false, "cetak DSN (password disamarkan) lalu keluar")
	flag.Parse()

	cfg, err := config.LoadDB()
	if err != nil {
		fmt.Fprintln(os.Stderr, "config:", err)
		os.Exit(1)
	}

	fmt.Printf("target: %s@%s:%d/%s\n\n", cfg.User, cfg.Host, cfg.Port, cfg.Name)
	if *dsnOnly {
		fmt.Printf("password terisi: %v\n", cfg.Password != "")
		return
	}

	ctx, cancel := context.WithTimeout(context.Background(), 15*time.Second)
	defer cancel()

	handle, err := db.Open(ctx, cfg)
	if err != nil {
		fmt.Fprintln(os.Stderr, "koneksi gagal:", err)
		os.Exit(1)
	}
	defer handle.Close()

	store := repo.New(handle)

	// 1. Ringkasan global + kelengkapan data
	ringkasan, took, err := timed(func() (repo.Ringkasan, error) { return store.Ringkasan(ctx) })
	if err != nil {
		fmt.Fprintln(os.Stderr, "ringkasan:", err)
		os.Exit(1)
	}

	fmt.Println("RINGKASAN")
	fmt.Printf("  umkm=%d  kategori=%d  kelompok=%d  rating=%d  aktivitas=%d\n",
		ringkasan.Umkm, ringkasan.Kategori, ringkasan.Kelompok, ringkasan.Rating, ringkasan.Aktivitas)
	fmt.Printf("  tanpa rating=%d  tanpa menu=%d  lokasi hilang=%d  rata klik/umkm=%.1f\n",
		ringkasan.TanpaRating, ringkasan.TanpaMenu, ringkasan.TanpaLokasi, ringkasan.RataKlikUmkm)
	fmt.Printf("  query: %s\n\n", took)

	// 2. Sebaran per kategori
	perKategori, took, err := timed(func() ([]repo.KategoriStat, error) { return store.PerKategori(ctx) })
	if err != nil {
		fmt.Fprintln(os.Stderr, "per kategori:", err)
		os.Exit(1)
	}

	w := tabwriter.NewWriter(os.Stdout, 0, 0, 2, ' ', 0)
	fmt.Fprintln(w, "ID\tKATEGORI\tKELOMPOK\tUMKM\tRATING\tRATA")
	for _, k := range perKategori {
		fmt.Fprintf(w, "%d\t%s\t%s\t%d\t%d\t%.2f\n", k.ID, k.Nama, k.Kelompok, k.JumlahUmkm, k.JumlahRating, k.RatingRata)
	}
	w.Flush()
	fmt.Printf("  query: %s\n\n", took)

	// 3. Leaderboard klik
	top, took, err := timed(func() ([]repo.UmkmKlik, error) { return store.TopKlik(ctx, *limit) })
	if err != nil {
		fmt.Fprintln(os.Stderr, "leaderboard:", err)
		os.Exit(1)
	}

	fmt.Printf("TOP %d KLIK\n", *limit)
	w = tabwriter.NewWriter(os.Stdout, 0, 0, 2, ' ', 0)
	fmt.Fprintln(w, "#\tUMKM\tKATEGORI\tKLIK")
	for i, u := range top {
		fmt.Fprintf(w, "%d\t%s\t%s\t%d\n", i+1, u.Nama, u.Kategori, u.TotalKlik)
	}
	w.Flush()
	fmt.Printf("  query: %s\n", took)
}

// timed mengukur durasi query: kebiasaan yang wajib dipakai sebelum optimasi.
func timed[T any](fn func() (T, error)) (T, time.Duration, error) {
	start := time.Now()
	v, err := fn()
	return v, time.Since(start).Round(time.Microsecond), err
}
