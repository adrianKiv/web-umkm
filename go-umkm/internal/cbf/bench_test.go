package cbf

import (
	"fmt"
	"testing"
)

// BenchmarkSkor mengukur algoritma murni tanpa DB. Dataset dibuat mirip
// production: 147 UMKM, 10 kategori, 3 kelompok.
func BenchmarkSkor(b *testing.B) {
	in := Input{
		KategoriKelompok: map[int]int{1: 1, 2: 1, 3: 1, 10: 1, 7: 2, 8: 2, 9: 2, 4: 3, 5: 3, 6: 3},
		KelompokMembers: map[int][]int{
			1: {2, 3, 1, 10},
			2: {9, 7, 8},
			3: {5, 6, 4},
		},
		Explicit: []int{1, 7},
		Implicit: []KategoriCount{{ID: 7, Total: 12}, {ID: 3, Total: 8}, {ID: 1, Total: 5}},
		Params:   DefaultParams(),
	}
	for i := 0; i < 147; i++ {
		in.Umkm = append(in.Umkm, Umkm{
			ID:         i + 1,
			Nama:       fmt.Sprintf("UMKM %03d", i+1),
			KategoriID: (i % 10) + 1,
			RatingAvg:  3.5 + float64(i%15)/10,
		})
	}

	b.ReportAllocs()
	b.ResetTimer()
	for i := 0; i < b.N; i++ {
		hasil := Skor(in)
		if len(hasil.Rekomendasi) == 0 {
			b.Fatal("rekomendasi kosong")
		}
	}
}
