// Command server = artefak Sprint 2.
// HTTP service rekomendasi CBF, membaca MySQL Laravel secara read-only.
//
// Jalankan dari folder go-umkm:
//
//	go run ./cmd/server                 # 127.0.0.1:8081, tanpa API key
//	REKOMENDASI_API_KEY=rahasia go run ./cmd/server -addr 0.0.0.0:8081
package main

import (
	"context"
	"errors"
	"flag"
	"log/slog"
	"net/http"
	"os"
	"os/signal"
	"strconv"
	"syscall"
	"time"

	"umkm/rekomendasi/internal/cbf"
	"umkm/rekomendasi/internal/config"
	"umkm/rekomendasi/internal/db"
	"umkm/rekomendasi/internal/httpapi"
	"umkm/rekomendasi/internal/repo"
)

func main() {
	addr := flag.String("addr", "127.0.0.1:8081", "alamat listen")
	flag.Parse()

	log := slog.New(slog.NewTextHandler(os.Stdout, &slog.HandlerOptions{Level: slog.LevelInfo}))

	apiKey := os.Getenv("REKOMENDASI_API_KEY")
	if err := httpapi.ValidasiBind(*addr, apiKey); err != nil {
		log.Error("konfigurasi tidak aman", "err", err)
		os.Exit(1)
	}

	cfg, err := config.LoadDB()
	if err != nil {
		log.Error("config", "err", err)
		os.Exit(1)
	}

	ctx, stop := signal.NotifyContext(context.Background(), os.Interrupt, syscall.SIGTERM)
	defer stop()

	handle, err := db.Open(ctx, cfg)
	if err != nil {
		log.Error("koneksi db", "err", err)
		os.Exit(1)
	}
	defer handle.Close()

	params := cbf.Params{
		ImplicitWeight: angkaEnv("CBF_IMPLICIT_WEIGHT", 0.10),
		SpilloverRate:  angkaEnv("CBF_SPILLOVER_RATE", 0.50),
		Allocation:     [3]int{4, 3, 3},
	}

	store := repo.New(handle)
	// REKOMENDASI_CACHE_TTL dalam detik; 0 = cache mati (tiap request query DB).
	ttlDetik := int(angkaEnv("REKOMENDASI_CACHE_TTL", 60))
	store.SetCacheTTL(time.Duration(ttlDetik) * time.Second)
	log.Info("cache", "ttl_detik", ttlDetik)

	srv := httpapi.New(store, apiKey, params, log)

	httpServer := &http.Server{
		Addr:              *addr,
		Handler:           srv.Handler(),
		ReadHeaderTimeout: 5 * time.Second,
		ReadTimeout:       10 * time.Second,
		WriteTimeout:      15 * time.Second,
		IdleTimeout:       60 * time.Second,
	}

	go func() {
		log.Info("server mulai", "addr", *addr, "api_key", apiKey != "")
		if err := httpServer.ListenAndServe(); err != nil && !errors.Is(err, http.ErrServerClosed) {
			log.Error("listen", "err", err)
			stop()
		}
	}()

	<-ctx.Done()
	log.Info("berhenti, tunggu request aktif selesai")

	shutdownCtx, cancel := context.WithTimeout(context.Background(), 10*time.Second)
	defer cancel()
	if err := httpServer.Shutdown(shutdownCtx); err != nil {
		log.Error("shutdown", "err", err)
	}
}

func angkaEnv(key string, fallback float64) float64 {
	raw := os.Getenv(key)
	if raw == "" {
		return fallback
	}
	v, err := strconv.ParseFloat(raw, 64)
	if err != nil {
		return fallback
	}
	return v
}
