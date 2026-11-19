// Package config membaca konfigurasi dari file .env Laravel.
// Tidak pakai library: cukup parser kecil, karena kebutuhan baru DB_* dan
// nanti URL service. Nilai environment asli selalu menang atas isi .env.
package config

import (
	"bufio"
	"errors"
	"os"
	"path/filepath"
	"strconv"
	"strings"
)

// DB adalah subset .env yang dibutuhkan service Go.
type DB struct {
	Host     string
	Port     int
	Name     string
	User     string
	Password string
	// TLS kosong = tanpa TLS (MySQL lokal). Azure Database for MySQL menolak
	// koneksi tanpa TLS, jadi di cloud isi DB_TLS=preferred (atau true).
	TLS string
}

// Pelajar: .env ada di root project Laravel, sedangkan `go run` dijalankan dari
// folder go-umkm. Jadi kita naik folder sampai ketemu .env.
const maxNaik = 4

// FindEnv mencari .env mulai dari direktori kerja lalu ke atas.
func FindEnv() (string, error) {
	dir, err := os.Getwd()
	if err != nil {
		return "", err
	}

	for i := 0; i <= maxNaik; i++ {
		candidate := filepath.Join(dir, ".env")
		if _, err := os.Stat(candidate); err == nil {
			return candidate, nil
		}
		parent := filepath.Dir(dir)
		if parent == dir {
			break
		}
		dir = parent
	}

	return "", errors.New(".env Laravel tidak ditemukan (jalankan dari dalam project)")
}

// LoadEnv membaca pasangan KEY=VALUE. Komentar (#), baris kosong, tanda kutip,
// dan export diabaikan/dibersihkan.
func LoadEnv(path string) (map[string]string, error) {
	f, err := os.Open(path)
	if err != nil {
		return nil, err
	}
	defer f.Close()

	values := make(map[string]string)
	scanner := bufio.NewScanner(f)
	for scanner.Scan() {
		line := strings.TrimSpace(scanner.Text())
		if line == "" || strings.HasPrefix(line, "#") {
			continue
		}
		line = strings.TrimPrefix(line, "export ")

		key, val, ok := strings.Cut(line, "=")
		if !ok {
			continue
		}
		key = strings.TrimSpace(key)
		val = strings.TrimSpace(val)

		// Buang komentar inline hanya kalau nilainya tidak dikutip.
		if !strings.HasPrefix(val, `"`) && !strings.HasPrefix(val, "'") {
			if i := strings.Index(val, " #"); i >= 0 {
				val = strings.TrimSpace(val[:i])
			}
		}
		values[key] = strings.Trim(val, `"'`)
	}

	return values, scanner.Err()
}

// LoadDB menggabungkan .env dengan environment nyata (env menang).
func LoadDB() (DB, error) {
	values := map[string]string{}
	if path, err := FindEnv(); err == nil {
		parsed, err := LoadEnv(path)
		if err != nil {
			return DB{}, err
		}
		values = parsed
	}

	get := func(key, fallback string) string {
		if v := os.Getenv(key); v != "" {
			return v
		}
		if v := values[key]; v != "" {
			return v
		}
		return fallback
	}

	port, err := strconv.Atoi(get("DB_PORT", "3306"))
	if err != nil {
		return DB{}, errors.New("DB_PORT bukan angka: " + get("DB_PORT", ""))
	}

	db := DB{
		Host:     get("DB_HOST", "127.0.0.1"),
		Port:     port,
		Name:     get("DB_DATABASE", ""),
		User:     get("DB_USERNAME", ""),
		Password: get("DB_PASSWORD", ""),
		TLS:      get("DB_TLS", ""),
	}
	if db.Name == "" {
		return DB{}, errors.New("DB_DATABASE kosong: cek .env")
	}

	return db, nil
}
