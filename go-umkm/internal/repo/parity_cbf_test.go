package repo

import (
	"context"
	"encoding/json"
	"os"
	"testing"
	"time"

	"umkm/rekomendasi/internal/cbf"
	"umkm/rekomendasi/internal/config"
	"umkm/rekomendasi/internal/db"
)

// Skenario digenerate dari data nyata oleh parity/make_scenarios.php,
// jawabannya oleh parity/cbf_reference.php (salinan verbatim landing()).
type skenario struct {
	Nama     string `json:"nama"`
	Explicit []int  `json:"explicit"`
	Session  string `json:"session_id"`
	UserID   int    `json:"user_id"`
}

type jawabanPHP struct {
	Nama        string `json:"nama"`
	TopKategori []int  `json:"top_category_ids"`
	Rekomendasi []struct {
		ID        int `json:"id_umkm"`
		Peringkat int `json:"peringkat"`
	} `json:"recommended"`
}

// TestParitasCBF: skor dan urutan rekomendasi Go harus sama dengan PHP.
// Butuh MySQL hidup + file parity hasil generate.
//
//	UMKM_DB_TEST=1 go test ./internal/repo -run TestParitasCBF -v
func TestParitasCBF(t *testing.T) {
	if os.Getenv("UMKM_DB_TEST") == "" {
		t.Skip("set UMKM_DB_TEST=1 untuk test integrasi")
	}

	rawSkenario, err := os.ReadFile("../../parity/cbf_scenarios.json")
	if err != nil {
		t.Fatalf("skenario belum digenerate: %v", err)
	}
	var skenarios []skenario
	if err := json.Unmarshal(rawSkenario, &skenarios); err != nil {
		t.Fatal(err)
	}

	rawPHP, err := os.ReadFile("../../parity/cbf_php.json")
	if err != nil {
		t.Fatalf("jawaban PHP belum digenerate: %v", err)
	}
	var jawaban []jawabanPHP
	if err := json.Unmarshal(rawPHP, &jawaban); err != nil {
		t.Fatal(err)
	}
	if len(jawaban) != len(skenarios) {
		t.Fatalf("jumlah jawaban PHP %d != skenario %d", len(jawaban), len(skenarios))
	}

	cfg, err := config.LoadDB()
	if err != nil {
		t.Fatalf("config: %v", err)
	}
	ctx, cancel := context.WithTimeout(context.Background(), 30*time.Second)
	defer cancel()

	handle, err := db.Open(ctx, cfg)
	if err != nil {
		t.Fatalf("koneksi: %v", err)
	}
	defer handle.Close()

	store := New(handle)
	params := cbf.DefaultParams()

	for i, sk := range skenarios {
		mau := jawaban[i]

		got, err := store.Rekomendasi(ctx, sk.Explicit, sk.UserID, sk.Session, params)
		if err != nil {
			t.Fatalf("%s: %v", sk.Nama, err)
		}

		if !samaInt(got.TopKategori, mau.TopKategori) {
			t.Errorf("%s: top kategori Go=%v PHP=%v", sk.Nama, got.TopKategori, mau.TopKategori)
			continue
		}
		if len(got.Rekomendasi) != len(mau.Rekomendasi) {
			t.Errorf("%s: jumlah rekomendasi Go=%d PHP=%d", sk.Nama, len(got.Rekomendasi), len(mau.Rekomendasi))
			continue
		}
		for j, item := range got.Rekomendasi {
			want := mau.Rekomendasi[j]
			if item.ID != want.ID || item.Peringkat != want.Peringkat {
				t.Errorf("%s: item %d Go={%d,%d} PHP={%d,%d}",
					sk.Nama, j, item.ID, item.Peringkat, want.ID, want.Peringkat)
			}
		}

		t.Logf("%-24s top=%v rekomendasi=%d item", sk.Nama, got.TopKategori, len(got.Rekomendasi))
	}

	t.Logf("%d skenario dibandingkan dengan PHP", len(skenarios))
}

func samaInt(a, b []int) bool {
	if len(a) != len(b) {
		return false
	}
	for i := range a {
		if a[i] != b[i] {
			return false
		}
	}
	return true
}
