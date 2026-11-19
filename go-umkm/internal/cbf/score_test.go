package cbf

import "testing"

// Peta kategori contoh: kelompok 1 = {1,2,3}, kelompok 2 = {7,8}, 9 tanpa kelompok.
func contohInput() Input {
	return Input{
		KategoriKelompok: map[int]int{1: 1, 2: 1, 3: 1, 7: 2, 8: 2},
		KelompokMembers:  map[int][]int{1: {1, 2, 3}, 2: {7, 8}},
		Params:           DefaultParams(),
		Umkm: []Umkm{
			{ID: 1, Nama: "A", KategoriID: 1, RatingAvg: 4.0},
			{ID: 2, Nama: "B", KategoriID: 1, RatingAvg: 4.8},
			{ID: 3, Nama: "C", KategoriID: 1, RatingAvg: 4.2},
			{ID: 4, Nama: "D", KategoriID: 1, RatingAvg: 3.9},
			{ID: 5, Nama: "E", KategoriID: 1, RatingAvg: 3.5},
			{ID: 6, Nama: "F", KategoriID: 2, RatingAvg: 4.1},
			{ID: 7, Nama: "G", KategoriID: 7, RatingAvg: 4.6},
			{ID: 8, Nama: "H", KategoriID: 7, RatingAvg: 4.6},
			{ID: 9, Nama: "I", KategoriID: 9, RatingAvg: 5.0},
		},
	}
}

func TestSkorEksplisitSaja(t *testing.T) {
	in := contohInput()
	in.Explicit = []int{1}

	hasil := Skor(in)

	// Kategori 1 skor 1.0; saudara 2 dan 3 dapat 0.5.
	if len(hasil.TopKategori) != 3 || hasil.TopKategori[0] != 1 {
		t.Fatalf("top kategori = %v, mau [1 2 3]", hasil.TopKategori)
	}
	// Alokasi 4-3-3: kategori 1 -> 4 item, kategori 2 -> 1 item (cuma ada 1),
	// kategori 3 -> 0. Total 5.
	if len(hasil.Rekomendasi) != 5 {
		t.Fatalf("rekomendasi %d item, mau 5", len(hasil.Rekomendasi))
	}
	if hasil.Rekomendasi[0].ID != 2 || hasil.Rekomendasi[0].RatingAvg != 4.8 {
		t.Errorf("item pertama = %+v, mau ID 2 rating 4.8", hasil.Rekomendasi[0])
	}
	for i := 1; i < 4; i++ {
		if hasil.Rekomendasi[i-1].RatingAvg < hasil.Rekomendasi[i].RatingAvg {
			t.Errorf("urutan rating tidak turun di posisi %d", i)
		}
	}
	if hasil.Rekomendasi[4].ID != 6 || hasil.Rekomendasi[4].Peringkat != 2 {
		t.Errorf("item ke-5 = %+v, mau ID 6 peringkat 2", hasil.Rekomendasi[4])
	}
}

func TestSkorImplisitDanSpillover(t *testing.T) {
	in := contohInput()
	in.Implicit = []KategoriCount{{ID: 7, Total: 12}} // 12 klik x 0.10 = 1.20

	hasil := Skor(in)

	// 12 x 0.1 = 1.2000000000000002 di IEEE-754 (sama seperti PHP), jadi
	// perbandingan pakai toleransi, bukan ==.
	if got := hasil.SkorKategori[0]; got.KategoriID != 7 || !dekat(got.Skor, 1.2) {
		t.Fatalf("skor kategori pertama = %+v, mau {7 1.2}", got)
	}
	// Spillover: 8 dapat 1.2*0.5 = 0.6, bukan berantai balik ke 7.
	var skor8, skor7 float64
	for _, s := range hasil.SkorKategori {
		switch s.KategoriID {
		case 7:
			skor7 = s.Skor
		case 8:
			skor8 = s.Skor
		}
	}
	if !dekat(skor7, 1.2) || !dekat(skor8, 0.6) {
		t.Errorf("skor 7=%.2f 8=%.2f, mau 1.20 dan 0.60", skor7, skor8)
	}
}

func TestSkorEksplisitPlusImplisitAkumulatif(t *testing.T) {
	in := contohInput()
	in.Explicit = []int{7}
	in.Implicit = []KategoriCount{{ID: 7, Total: 5}} // 1.0 + 0.5

	hasil := Skor(in)

	// 7 = 1.5, 8 = 0.75, kategori lain tidak dapat.
	if len(hasil.SkorKategori) != 2 {
		t.Fatalf("jumlah kategori berskor = %d, mau 2", len(hasil.SkorKategori))
	}
	if !dekat(hasil.SkorKategori[0].Skor, 1.5) {
		t.Errorf("skor kategori 7 = %.2f, mau 1.50", hasil.SkorKategori[0].Skor)
	}
}

func TestSkorKategoriTanpaAnggota(t *testing.T) {
	in := contohInput()
	in.KategoriKelompok = map[int]int{1: 1}
	in.KelompokMembers = map[int][]int{1: {1}}
	in.Explicit = []int{1}

	hasil := Skor(in)

	// Tidak ada spillover, dan kategori 9 tetap bisa muncul kalau skornya nol?
	// Tidak: hanya kategori dengan skor > 0 yang masuk final.
	for _, s := range hasil.SkorKategori {
		if s.KategoriID != 1 {
			t.Errorf("kategori %d seharusnya tidak punya skor", s.KategoriID)
		}
	}
}

func TestSkorKosong(t *testing.T) {
	hasil := Skor(contohInput())

	if len(hasil.TopKategori) != 0 || len(hasil.Rekomendasi) != 0 {
		t.Errorf("tanpa preferensi harus kosong, dapat top=%v rekomendasi=%d",
			hasil.TopKategori, len(hasil.Rekomendasi))
	}
}

func TestTieBreakRatingStabil(t *testing.T) {
	in := contohInput()
	in.Explicit = []int{7}

	hasil := Skor(in)

	// Dua UMKM kategori 7 punya rating sama (4.6): urutan harus ikut urutan
	// kandidat (nama asc), bukan acak.
	if len(hasil.Rekomendasi) < 2 {
		t.Fatalf("rekomendasi kurang dari 2 item")
	}
	if hasil.Rekomendasi[0].ID != 7 || hasil.Rekomendasi[1].ID != 8 {
		t.Errorf("tie-break meleset: %d, %d", hasil.Rekomendasi[0].ID, hasil.Rekomendasi[1].ID)
	}
}

// dekat = perbandingan float dengan toleransi 1e-9 (akumulasi 0.1 tidak eksak).
func dekat(a, b float64) bool {
	d := a - b
	return d < 1e-9 && d > -1e-9
}
