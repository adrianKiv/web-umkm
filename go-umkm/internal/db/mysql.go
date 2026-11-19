// Package db membuka koneksi MySQL dengan pengaturan pool yang wajar.
// Bedanya dengan PDO Laravel: pool dikelola program kita sendiri, jadi
// parameter di bawah ini yang menentukan perilaku saat traffic naik.
package db

import (
	"context"
	"database/sql"
	"net"
	"strconv"
	"time"

	"github.com/go-sql-driver/mysql"

	"umkm/rekomendasi/internal/config"
)

// Open membuat *sql.DB (bukan satu koneksi) lalu memastikan server hidup.
func Open(ctx context.Context, cfg config.DB) (*sql.DB, error) {
	dsnCfg := mysql.Config{
		User:                 cfg.User,
		Passwd:               cfg.Password,
		Net:                  "tcp",
		Addr:                 net.JoinHostPort(cfg.Host, strconv.Itoa(cfg.Port)),
		DBName:               cfg.Name,
		ParseTime:            true,
		Loc:                  time.Local,
		AllowNativePasswords: true,
		Collation:            "utf8mb4_general_ci",
	}

	// Azure Database for MySQL menolak koneksi tanpa TLS (error 3159),
	// jadi mode TLS dibuat bisa diatur, bukan dipaksa, agar benchmark lokal
	// tetap memakai jalur tanpa TLS.
	if cfg.TLS != "" {
		dsnCfg.TLSConfig = cfg.TLS
	}

	handle, err := sql.Open("mysql", dsnCfg.FormatDSN())
	if err != nil {
		return nil, err
	}

	handle.SetMaxOpenConns(10)
	handle.SetMaxIdleConns(5)
	handle.SetConnMaxLifetime(30 * time.Minute)

	pingCtx, cancel := context.WithTimeout(ctx, 5*time.Second)
	defer cancel()

	if err := handle.PingContext(pingCtx); err != nil {
		handle.Close()
		return nil, err
	}

	return handle, nil
}
