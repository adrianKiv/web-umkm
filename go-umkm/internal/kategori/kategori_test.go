package kategori

import "testing"

func TestMap(t *testing.T) {
	tests := []struct {
		nama    string
		amenity string
		want    int
	}{
		{"Warung Nasi Ibu Imas", "restaurant", Nasi},
		{"Nasi Goreng Pak Jaja", "", Nasi},
		{"Kopi Kenangan", "cafe", Kopi},
		{"Café Sudirman", "cafe", Kopi},
		// Temuan audit: kata kuncinya "kopi", bukan "cafe". Nama berawalan
		// "Café" tanpa tag amenity jatuh ke default Nasi (sama seperti PHP).
		{"Café Sudirman", "", Nasi},
		{"Kopi Aci", "", Kopi}, // kopi menang duluan, sama seperti PHP
		{"Bakso Pak Kumis", "restaurant", Mie},
		{"Mie Ayam Pak Tono", "", Mie}, // "mie" menang sebelum "ayam"
		{"Seblak Neng Ana", "", Seblak},
		{"Kedai Aci Rasa", "", Seblak},
		{"Ayam Geprek Bensu", "fast_food", Ayam},
		{"Warung Serba Ada", "fast_food", Ayam}, // amenity fast_food
		{"Warteg Bahari", "", Nasi},
		{"AYAM Penyet Mas Budi", "", Ayam}, // bukti asciiLower jalan
		{"KOPI Tuku", "", Kopi},
	}

	for _, tt := range tests {
		if got := Map(tt.amenity, tt.nama); got != tt.want {
			t.Errorf("Map(%q, %q) = %d, mau %d", tt.amenity, tt.nama, got, tt.want)
		}
	}
}

func TestSlug(t *testing.T) {
	tests := []struct {
		nama string
		want string
	}{
		{"Warung Nasi Ibu Imas", "warung-nasi-ibu-imas"},
		{"RM. Padang Sederhana", "rm-padang-sederhana"},
		{"Sate & Gule H. Mahmud", "sate-gule-h-mahmud"},
		{"Café Sudirman", "cafe-sudirman"},
		{"  Kopi   Kenangan  ", "kopi-kenangan"},
		{"Ayam Geprek Bensu!", "ayam-geprek-bensu"},
		{"Warung 24 Jam", "warung-24-jam"},
		{"Toko Sembako (Murah)", "toko-sembako-murah"},
	}

	for _, tt := range tests {
		if got := Slug(tt.nama); got != tt.want {
			t.Errorf("Slug(%q) = %q, mau %q", tt.nama, got, tt.want)
		}
	}
}

func TestSlugWithID(t *testing.T) {
	got := SlugWithID("Kopi Kenangan", 1102938472)
	want := "kopi-kenangan-1102938472"
	if got != want {
		t.Fatalf("SlugWithID = %q, mau %q", got, want)
	}
}
