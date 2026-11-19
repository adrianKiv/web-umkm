// Package cache = TTL cache kecil untuk data yang jarang berubah
// (peta kategori, agregat rating). Tanpa dependency luar.
//
// Kenapa perlu: tiap request rekomendasi sebelumnya menembak 3 query MySQL.
// Peta kategori dan rata-rata rating berubah hanya saat admin menambah/edit
// data, jadi cukup di-cache beberapa puluh detik.
package cache

import (
	"context"
	"sync"
	"time"
)

// TTL menyimpan satu nilai. Get memuat lewat loader kalau belum ada atau
// sudah kedaluwarsa. Load dijalankan di dalam lock supaya 100 request yang
// datang bersamaan tidak menghasilkan 100 query (anti stampede); konsekuensinya
// request lain menunggu satu load selesai — trade-off yang wajar untuk data
// yang load-nya puluhan milidetik.
type TTL[T any] struct {
	mu     sync.Mutex
	ttl    time.Duration
	now    func() time.Time // bisa diganti di test
	loaded bool
	val    T
	exp    time.Time
}

// New membuat cache dengan umur ttl. ttl <= 0 berarti cache dimatikan:
// setiap Get memanggil loader (perilaku sama seperti sebelum ada cache).
func New[T any](ttl time.Duration) *TTL[T] {
	return &TTL[T]{ttl: ttl, now: time.Now}
}

// Get mengembalikan nilai dari cache, atau memuatnya lebih dulu.
// Nilai yang dikembalikan dipakai read-only oleh pemanggil; jangan dimutasi.
func (c *TTL[T]) Get(ctx context.Context, loader func(context.Context) (T, error)) (T, error) {
	if c.ttl <= 0 {
		return loader(ctx)
	}

	c.mu.Lock()
	defer c.mu.Unlock()

	if c.loaded && c.now().Before(c.exp) {
		return c.val, nil
	}

	val, err := loader(ctx)
	if err != nil {
		// Jangan simpan error: request berikutnya boleh coba lagi.
		// Nilai lama (kalau ada) juga tidak dipakai supaya data basi tidak
		// diam-diam disajikan saat DB bermasalah.
		var kosong T
		return kosong, err
	}

	c.val = val
	c.loaded = true
	c.exp = c.now().Add(c.ttl)
	return val, nil
}

// Reset memaksa load ulang pada Get berikutnya.
func (c *TTL[T]) Reset() {
	c.mu.Lock()
	c.loaded = false
	c.mu.Unlock()
}
