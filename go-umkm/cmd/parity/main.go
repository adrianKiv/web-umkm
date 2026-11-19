// Command parity mencetak hasil porting (kategori + slug) sebagai JSON.
// Dibandingkan dengan output parity/php_fixture.php: harus identik byte demi byte.
package main

import (
	"encoding/json"
	"fmt"
	"os"

	"umkm/rekomendasi/internal/kategori"
)

type sample struct {
	Nama    string `json:"nama"`
	Amenity string `json:"amenity"`
	OsmID   int64  `json:"osm_id"`
}

type result struct {
	Nama     string `json:"nama"`
	Amenity  string `json:"amenity"`
	Kategori int    `json:"kategori"`
	Slug     string `json:"slug"`
}

func main() {
	path := "parity/samples.json"
	if len(os.Args) > 1 {
		path = os.Args[1]
	}

	raw, err := os.ReadFile(path)
	if err != nil {
		fmt.Fprintln(os.Stderr, "baca samples:", err)
		os.Exit(1)
	}

	var samples []sample
	if err := json.Unmarshal(raw, &samples); err != nil {
		fmt.Fprintln(os.Stderr, "parse samples:", err)
		os.Exit(1)
	}

	out := make([]result, 0, len(samples))
	for _, s := range samples {
		out = append(out, result{
			Nama:     s.Nama,
			Amenity:  s.Amenity,
			Kategori: kategori.Map(s.Amenity, s.Nama),
			Slug:     kategori.SlugWithID(s.Nama, s.OsmID),
		})
	}

	enc := json.NewEncoder(os.Stdout)
	enc.SetIndent("", "  ")
	enc.SetEscapeHTML(false)
	if err := enc.Encode(out); err != nil {
		fmt.Fprintln(os.Stderr, "encode:", err)
		os.Exit(1)
	}
}
