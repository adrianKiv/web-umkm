// Command loadtest = alat ukur Sprint 5.
// Menembak endpoint rekomendasi dengan N request dan C worker paralel,
// lalu melaporkan p50/p95/p99, throughput, dan jumlah error.
//
// Contoh (dari folder go-umkm):
//
//	go run ./cmd/loadtest -n 500 -c 10
//	go run ./cmd/loadtest -n 500 -c 50 -body '{"explicit_categories":[1,7]}'
package main

import (
	"bytes"
	"flag"
	"fmt"
	"io"
	"net/http"
	"os"
	"sort"
	"strings"
	"sync"
	"time"
)

type hasil struct {
	durasi []time.Duration
	status map[int]int
	mu     sync.Mutex
}

func (h *hasil) catat(d time.Duration, status int) {
	h.mu.Lock()
	h.durasi = append(h.durasi, d)
	h.status[status]++
	h.mu.Unlock()
}

func main() {
	url := flag.String("url", "http://127.0.0.1:8081/api/v1/rekomendasi", "endpoint")
	n := flag.Int("n", 500, "jumlah request total")
	c := flag.Int("c", 10, "worker paralel")
	body := flag.String("body", `{"explicit_categories":[1,7]}`, "body JSON")
	label := flag.String("label", "", "label hasil")
	flag.Parse()

	client := &http.Client{
		Timeout: 10 * time.Second,
		Transport: &http.Transport{
			MaxIdleConns:        200,
			MaxIdleConnsPerHost: 200,
			MaxConnsPerHost:     200,
		},
	}

	// Pemanasan: 20 request supaya koneksi & cache siap.
	for i := 0; i < 20; i++ {
		resp, err := client.Post(*url, "application/json", bytes.NewReader([]byte(*body)))
		if err == nil {
			io.Copy(io.Discard, resp.Body)
			resp.Body.Close()
		}
	}

	h := &hasil{status: map[int]int{}}
	jobs := make(chan struct{}, *n)
	var wg sync.WaitGroup

	mulai := time.Now()
	for w := 0; w < *c; w++ {
		wg.Add(1)
		go func() {
			defer wg.Done()
			for range jobs {
				t0 := time.Now()
				resp, err := client.Post(*url, "application/json", bytes.NewReader([]byte(*body)))
				selesai := time.Since(t0)
				if err != nil {
					h.catat(selesai, 0)
					continue
				}
				io.Copy(io.Discard, resp.Body)
				resp.Body.Close()
				h.catat(selesai, resp.StatusCode)
			}
		}()
	}
	for i := 0; i < *n; i++ {
		jobs <- struct{}{}
	}
	close(jobs)
	wg.Wait()
	total := time.Since(mulai)

	d := h.durasi
	sort.Slice(d, func(i, j int) bool { return d[i] < d[j] })

	ambil := func(p float64) time.Duration {
		if len(d) == 0 {
			return 0
		}
		i := int(float64(len(d)-1) * p)
		return d[i]
	}

	labelTeks := *label
	if labelTeks == "" {
		labelTeks = fmt.Sprintf("c=%d", *c)
	}

	sukses := h.status[200]
	if sukses == 0 {
		fmt.Printf("%-28s SEMUA GAGAL  status=%v\n", labelTeks, h.status)
		os.Exit(2)
	}

	fmt.Printf("%-28s n=%d ok=%d err=%d  p50=%-8s p95=%-8s p99=%-8s max=%-8s  rps_ok=%6.0f  status=%v\n",
		labelTeks, len(d), sukses, len(d)-sukses,
		ambil(0.50).Round(10*time.Microsecond),
		ambil(0.95).Round(10*time.Microsecond),
		ambil(0.99).Round(10*time.Microsecond),
		d[len(d)-1].Round(10*time.Microsecond),
		float64(sukses)/total.Seconds(),
		ringkasStatus(h.status),
	)

	if *label != "" {
		// Simpan mentah ke file untuk diolah lagi kalau perlu.
		var sb strings.Builder
		for _, x := range d {
			fmt.Fprintf(&sb, "%d\n", x.Microseconds())
		}
		nama := fmt.Sprintf("bench_%s.txt", strings.ReplaceAll(*label, " ", "_"))
		if err := os.WriteFile(nama, []byte(sb.String()), 0o644); err != nil {
			fmt.Fprintln(os.Stderr, "gagal simpan:", err)
		}
	}
}

func ringkasStatus(m map[int]int) string {
	if len(m) == 1 {
		for k, v := range m {
			return fmt.Sprintf("%dx%d", v, k)
		}
	}
	return fmt.Sprint(m)
}
