// Package httpapi membungkus rekomendasi sebagai HTTP service.
// Kontrak: POST /api/v1/rekomendasi, header X-API-Key, body JSON kecil.
package httpapi

import (
	"context"
	"encoding/json"
	"errors"
	"log/slog"
	"net"
	"net/http"
	"time"

	"umkm/rekomendasi/internal/cbf"
	"umkm/rekomendasi/internal/repo"
)

// Server menyimpan dependensi handler: store DB + parameter scoring.
type Server struct {
	store  *repo.Store
	apiKey string
	params cbf.Params
	log    *slog.Logger
}

func New(store *repo.Store, apiKey string, params cbf.Params, log *slog.Logger) *Server {
	return &Server{store: store, apiKey: apiKey, params: params, log: log}
}

// Handler memasang route + middleware.
func (s *Server) Handler() http.Handler {
	mux := http.NewServeMux()
	mux.HandleFunc("GET /healthz", s.health)
	mux.Handle("POST /api/v1/rekomendasi", s.withAuth(http.HandlerFunc(s.rekomendasi)))
	return s.withLogging(mux)
}

type permintaan struct {
	ExplicitCategories []int  `json:"explicit_categories"`
	SessionID          string `json:"session_id"`
	UserID             int    `json:"user_id"`
}

type balasan struct {
	cbf.Hasil
	LamaMS float64 `json:"lama_ms"`
}

func (s *Server) rekomendasi(w http.ResponseWriter, r *http.Request) {
	start := time.Now()

	var in permintaan
	dec := json.NewDecoder(http.MaxBytesReader(w, r.Body, 64<<10))
	dec.DisallowUnknownFields()
	if err := dec.Decode(&in); err != nil {
		writeJSON(w, http.StatusBadRequest, map[string]string{"error": "body tidak valid: " + err.Error()})
		return
	}

	// Batasi jumlah kategori eksplisit: tamu bisa mengirim apa saja.
	if len(in.ExplicitCategories) > 50 {
		writeJSON(w, http.StatusBadRequest, map[string]string{"error": "explicit_categories maksimal 50"})
		return
	}

	hasil, err := s.store.Rekomendasi(r.Context(), in.ExplicitCategories, in.UserID, in.SessionID, s.params)
	if err != nil {
		s.log.Error("rekomendasi gagal", "err", err, "session", in.SessionID)
		writeJSON(w, http.StatusInternalServerError, map[string]string{"error": "gagal menghitung rekomendasi"})
		return
	}

	writeJSON(w, http.StatusOK, balasan{Hasil: hasil, LamaMS: ms(time.Since(start))})
}

func (s *Server) health(w http.ResponseWriter, r *http.Request) {
	ctx, cancel := context.WithTimeout(r.Context(), 2*time.Second)
	defer cancel()

	if err := s.store.Ping(ctx); err != nil {
		writeJSON(w, http.StatusServiceUnavailable, map[string]string{"status": "db down"})
		return
	}
	writeJSON(w, http.StatusOK, map[string]string{"status": "ok"})
}

// withAuth: API key wajib kalau dikonfigurasi. Tanpa key, server hanya boleh
// di-bind ke localhost (dicek di cmd/server).
func (s *Server) withAuth(next http.Handler) http.Handler {
	return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		if s.apiKey != "" && r.Header.Get("X-API-Key") != s.apiKey {
			writeJSON(w, http.StatusUnauthorized, map[string]string{"error": "unauthorized"})
			return
		}
		next.ServeHTTP(w, r)
	})
}

func (s *Server) withLogging(next http.Handler) http.Handler {
	return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		start := time.Now()
		next.ServeHTTP(w, r)
		s.log.Info("request", "method", r.Method, "path", r.URL.Path, "durasi", time.Since(start).String())
	})
}

func writeJSON(w http.ResponseWriter, status int, v any) {
	w.Header().Set("Content-Type", "application/json; charset=utf-8")
	w.WriteHeader(status)
	_ = json.NewEncoder(w).Encode(v)
}

func ms(d time.Duration) float64 {
	return float64(d.Microseconds()) / 1000
}

// IsLoopback mengecek alamat bind, dipakai main() untuk menolak 0.0.0.0
// saat API key tidak diisi.
func IsLoopback(addr string) bool {
	host, _, err := net.SplitHostPort(addr)
	if err != nil {
		return false
	}
	if host == "localhost" {
		return true
	}
	ip := net.ParseIP(host)
	return ip != nil && ip.IsLoopback()
}

var errTanpaKey = errors.New("API key kosong: bind ke 127.0.0.1 saja atau set REKOMENDASI_API_KEY")

// ValidasiBind memastikan kombinasi alamat + key aman.
func ValidasiBind(addr, apiKey string) error {
	if apiKey == "" && !IsLoopback(addr) {
		return errTanpaKey
	}
	return nil
}
