package main

import (
	"fmt"
	"os"
	"text/tabwriter"

	"umkm/rekomendasi/internal/kategori"
)

// Sampel nama + tag amenity, diambil dari pola data OpenStreetMap
// yang dipakai OsmController::sinkronisasiOsm().
type sample struct {
	nama    string
	amenity string
	osmID   int64
}

func main() {
	samples := []sample{
		{"Warung Nasi Ibu Imas", "restaurant", 1102938471},
		{"Kopi Kenangan", "cafe", 1102938472},
		{"Bakso Pak Kumis", "restaurant", 1102938473},
		{"Seblak Neng Ana", "", 1102938474},
		{"Ayam Geprek Bensu", "fast_food", 1102938475},
		{"Mie Ayam Pak Tono", "", 1102938476},
		{"RM. Padang Sederhana", "", 1102938477},
		{"Sate & Gule H. Mahmud", "restaurant", 1102938478},
		{"Café Sudirman", "", 1102938479},
		{"Kedai Aci Rasa", "", 1102938480},
	}

	w := tabwriter.NewWriter(os.Stdout, 0, 0, 2, ' ', 0)
	fmt.Fprintln(w, "NAMA\tAMENITY\tKATEGORI\tSLUG-OSM")
	fmt.Fprintln(w, "----\t-------\t--------\t--------")

	perKategori := map[int]int{}

	for _, s := range samples {
		id := kategori.Map(s.amenity, s.nama)
		perKategori[id]++
		fmt.Fprintf(w, "%s\t%s\t%d\t%s\n",
			s.nama,
			orDash(s.amenity),
			id,
			kategori.SlugWithID(s.nama, s.osmID),
		)
	}

	w.Flush()

	fmt.Println("\nSebaran:")
	for _, id := range []int{kategori.Nasi, kategori.Ayam, kategori.Mie, kategori.Seblak, kategori.Kopi} {
		fmt.Printf("  kategori %d (%s): %d\n", id, kategori.Name(id), perKategori[id])
	}
}

func orDash(s string) string {
	if s == "" {
		return "-"
	}
	return s
}
