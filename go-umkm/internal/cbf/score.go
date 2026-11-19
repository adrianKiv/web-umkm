// Package cbf = content-based filtering, port dari blok scoring di
// DataUmkmController::landing() (baris 98-172).
//
// Aturan porting: urutan operasi dan tie-break harus sama dengan PHP.
// PHP memakai array berurutan (insertion order) + uasort stabil, jadi di sini
// dipakai map + slice key untuk mereproduksi urutan yang sama.
package cbf

import "sort"

// Params = parameter yang di production ditulis sebagai angka literal.
// Dijadikan field supaya Sprint 5 (uji sensitivitas) bisa memvariasikannya.
type Params struct {
	ImplicitWeight float64 // 0.10
	SpilloverRate  float64 // 0.50
	Allocation     [3]int  // [4, 3, 3]
}

func DefaultParams() Params {
	return Params{ImplicitWeight: 0.10, SpilloverRate: 0.50, Allocation: [3]int{4, 3, 3}}
}

// Umkm = data minimum untuk scoring. RatingAvg = AVG(nilai_rating), 0 kalau
// belum ada rating (sama dengan `?? 0` di PHP).
type Umkm struct {
	ID         int
	Nama       string
	KategoriID int
	RatingAvg  float64
}

// KategoriCount = satu baris GROUP BY user_activities. Urutannya DIPERTAHANKAN
// dari MySQL, karena PHP memakai urutan itu untuk tie-break kategori.
type KategoriCount struct {
	ID    int
	Total int
}

// Input = semua bahan scoring. Umkm harus sudah terurut seperti query landing():
// ORDER BY nama_umkm, id_umkm — tie-break rating bergantung pada urutan ini.
type Input struct {
	Explicit         []int           // preferensi eksplisit (session/cookie), tiap item skor 1.0
	Implicit         []KategoriCount // hasil GROUP BY detail_click, urut baris MySQL
	KategoriKelompok map[int]int     // id_kategori -> id_kelompok
	KelompokMembers  map[int][]int   // id_kelompok -> daftar id_kategori (urut nama_kategori)
	Umkm             []Umkm
	Params           Params
}

// Item = satu UMKM yang masuk daftar rekomendasi.
type Item struct {
	ID         int     `json:"id_umkm"`
	Nama       string  `json:"nama_umkm"`
	KategoriID int     `json:"id_kategori"`
	RatingAvg  float64 `json:"rating_avg"`
	Skor       float64 `json:"skor"`
	Peringkat  int     `json:"peringkat_kategori"` // 1..3
}

// Hasil = keluaran yang dipakai Laravel + diagnostik untuk skripsi.
type Hasil struct {
	TopKategori  []int          `json:"top_category_ids"`
	SkorKategori []SkorKategori `json:"skor_kategori"`
	Rekomendasi  []Item         `json:"recommended"`
	Diagnostics  Diagnos        `json:"diagnostics"`
}

type SkorKategori struct {
	KategoriID int     `json:"id_kategori"`
	Skor       float64 `json:"skor"`
}

type Diagnos struct {
	ImplicitWeight float64 `json:"implicit_weight"`
	SpilloverRate  float64 `json:"spillover_rate"`
	Allocation     [3]int  `json:"allocation"`
	JumlahKandidat int     `json:"jumlah_kandidat"`
}

// skorTerurut = map skor yang mengingat urutan penyisipan, meniru array PHP.
type skorTerurut struct {
	keys []int
	vals map[int]float64
}

func newSkorTerurut() *skorTerurut {
	return &skorTerurut{vals: make(map[int]float64)}
}

func (s *skorTerurut) tambah(id int, delta float64) {
	if _, ada := s.vals[id]; !ada {
		s.keys = append(s.keys, id)
	}
	s.vals[id] += delta
}

func (s *skorTerurut) ambil(id int) (float64, bool) {
	v, ok := s.vals[id]
	return v, ok
}

// Skor menjalankan urutan yang sama dengan landing():
// eksplisit -> implisit -> spillover kelompok -> top 3 kategori -> alokasi 4-3-3.
func Skor(in Input) Hasil {
	p := in.Params
	if p.Allocation == [3]int{} {
		p = DefaultParams()
	}

	// A. Skor dasar: eksplisit 1.0 per kategori.
	base := newSkorTerurut()
	for _, id := range in.Explicit {
		base.tambah(id, 1.0)
	}

	// B. Implisit: jumlah klik x implicit_weight, ditambahkan ke skor yang ada.
	//    Urutan baris dari MySQL dipertahankan: di PHP urutan ini menentukan
	//    siapa yang menang saat dua kategori skornya seri (sortDesc stabil).
	for _, row := range in.Implicit {
		base.tambah(row.ID, float64(row.Total)*p.ImplicitWeight)
	}

	// C. Spillover: kategori yang skornya > 0 menyiram skor*0.50 ke saudara
	//    sekelompok. PENTING: PHP mengiterasi salinan skor dasar, bukan skor
	//    yang sedang tumbuh — jadi hasil spillover tidak dihitung berantai.
	final := newSkorTerurut()
	for _, id := range base.keys {
		final.tambah(id, base.vals[id])
	}
	for _, id := range base.keys {
		skor := base.vals[id]
		if skor <= 0 {
			continue
		}
		kelompokID, ada := in.KategoriKelompok[id]
		if !ada {
			continue
		}
		for _, saudara := range in.KelompokMembers[kelompokID] {
			if saudara == id {
				continue
			}
			final.tambah(saudara, skor*p.SpilloverRate)
		}
	}

	// D. Top 3 kategori: urut skor desc, stabil (seri = urutan penyisipan).
	top := make([]int, len(final.keys))
	copy(top, final.keys)
	sort.SliceStable(top, func(i, j int) bool { return final.vals[top[i]] > final.vals[top[j]] })
	if len(top) > 3 {
		top = top[:3]
	}

	// E. Alokasi 4-3-3, tiap kategori diurut rating desc (stabil: seri ikut
	//    urutan nama_umkm dari query).
	rekomendasi := make([]Item, 0, p.Allocation[0]+p.Allocation[1]+p.Allocation[2])
	for i, kategoriID := range top {
		limit := p.Allocation[2]
		if i < len(p.Allocation) {
			limit = p.Allocation[i]
		}

		kandidat := make([]Umkm, 0, len(in.Umkm))
		for _, u := range in.Umkm {
			if u.KategoriID == kategoriID {
				kandidat = append(kandidat, u)
			}
		}
		sort.SliceStable(kandidat, func(a, b int) bool { return kandidat[a].RatingAvg > kandidat[b].RatingAvg })

		if len(kandidat) > limit {
			kandidat = kandidat[:limit]
		}
		for _, u := range kandidat {
			skor, _ := final.ambil(u.KategoriID)
			rekomendasi = append(rekomendasi, Item{
				ID:         u.ID,
				Nama:       u.Nama,
				KategoriID: u.KategoriID,
				RatingAvg:  u.RatingAvg,
				Skor:       skor,
				Peringkat:  i + 1,
			})
		}
	}

	skorKategori := make([]SkorKategori, 0, len(final.keys))
	for _, id := range final.keys {
		skorKategori = append(skorKategori, SkorKategori{KategoriID: id, Skor: final.vals[id]})
	}

	return Hasil{
		TopKategori:  top,
		SkorKategori: skorKategori,
		Rekomendasi:  rekomendasi,
		Diagnostics: Diagnos{
			ImplicitWeight: p.ImplicitWeight,
			SpilloverRate:  p.SpilloverRate,
			Allocation:     p.Allocation,
			JumlahKandidat: len(in.Umkm),
		},
	}
}
