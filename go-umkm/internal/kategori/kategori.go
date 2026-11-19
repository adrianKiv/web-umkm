// Package kategori mem-port fungsi murni dari OsmController.php:
// getKategoriId() dan Str::slug() + osm_id.
//
// Aturan porting: jangan "memperbaiki" logika. Urutan if dan perbandingan
// string harus identik dengan PHP, kalau tidak data hasil sync akan beda
// dari data yang sudah ada di production.
package kategori

import "strings"

// ID kategori sesuai tabel kategori di database (id 1-10).
const (
	Nasi   = 1 // Nasi & Warteg (default)
	Ayam   = 2 // Ayam / Fastfood
	Mie    = 3 // Mie & Bakso
	Seblak = 4 // Seblak & Baci
	Kopi   = 7 // Kopi & Cafe
)

// Name hanya untuk tampilan CLI, bukan sumber kebenaran (itu tabel kategori).
func Name(id int) string {
	switch id {
	case Nasi:
		return "Nasi & Warteg"
	case Ayam:
		return "Ayam / Fastfood"
	case Mie:
		return "Mie & Bakso"
	case Seblak:
		return "Seblak & Baci"
	case Kopi:
		return "Kopi & Cafe"
	default:
		return "tidak dikenal"
	}
}

// Map adalah port 1:1 dari OsmController::getKategoriId().
// PHP: strpos(strtolower($nama), 'x') !== false, lalu cek amenity.
func Map(amenity, nama string) int {
	namaLower := asciiLower(nama)

	if strings.Contains(namaLower, "kopi") || amenity == "cafe" {
		return Kopi
	}
	if strings.Contains(namaLower, "mie") || strings.Contains(namaLower, "bakso") {
		return Mie
	}
	if strings.Contains(namaLower, "seblak") || strings.Contains(namaLower, "aci") {
		return Seblak
	}
	if strings.Contains(namaLower, "ayam") || amenity == "fast_food" {
		return Ayam
	}

	return Nasi
}
