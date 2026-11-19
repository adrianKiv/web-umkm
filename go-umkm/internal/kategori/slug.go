package kategori

import (
	"strconv"
	"strings"
)

// accentMap meniru Str::ascii() untuk karakter Latin yang sering muncul di
// nama UMKM (warung, kafe, nama orang). Cakupan sengaja kecil + tabel jelas,
// bukan transliterasi penuh: paritas hanya butuh kasus yang benar-benar ada.
var accentMap = map[rune]string{
	'á': "a", 'à': "a", 'â': "a", 'ä': "a", 'ã': "a", 'å': "a", 'ā': "a",
	'é': "e", 'è': "e", 'ê': "e", 'ë': "e", 'ē': "e",
	'í': "i", 'ì': "i", 'î': "i", 'ï': "i", 'ī': "i",
	'ó': "o", 'ò': "o", 'ô': "o", 'ö': "o", 'õ': "o", 'ø': "o", 'ō': "o",
	'ú': "u", 'ù': "u", 'û': "u", 'ü': "u", 'ū': "u",
	'ñ': "n", 'ç': "c", 'ý': "y", 'ÿ': "y",
	'Á': "a", 'À': "a", 'Â': "a", 'Ä': "a", 'Ã': "a", 'Å': "a",
	'É': "e", 'È': "e", 'Ê': "e", 'Ë': "e",
	'Í': "i", 'Ì': "i", 'Î': "i", 'Ï': "i",
	'Ó': "o", 'Ò': "o", 'Ô': "o", 'Ö': "o", 'Õ': "o", 'Ø': "o",
	'Ú': "u", 'Ù': "u", 'Û': "u", 'Ü': "u",
	'Ñ': "n", 'Ç': "c", 'Ý': "y",
}

// Slug adalah port Str::slug($nama): transliterasi -> lowercase ->
// non-alnum jadi '-' -> rapikan '-' berlebih.
func Slug(nama string) string {
	var b strings.Builder
	b.Grow(len(nama))

	lastDash := false
	for _, r := range nama {
		if rep, ok := accentMap[r]; ok {
			b.WriteString(rep)
			lastDash = false
			continue
		}

		switch {
		case r >= 'a' && r <= 'z', r >= '0' && r <= '9':
			b.WriteRune(r)
			lastDash = false
		case r >= 'A' && r <= 'Z':
			b.WriteRune(r + ('a' - 'A'))
			lastDash = false
		default:
			// spasi, titik, &, /, tanda kutip, emoji, dst -> satu '-'
			if !lastDash && b.Len() > 0 {
				b.WriteByte('-')
				lastDash = true
			}
		}
	}

	return strings.Trim(b.String(), "-")
}

// SlugWithID = Str::slug($nama) . '-' . $osmId, seperti di OsmController.
func SlugWithID(nama string, osmID int64) string {
	return Slug(nama) + "-" + strconv.FormatInt(osmID, 10)
}

// asciiLower meniru strtolower() PHP: hanya A-Z yang diubah.
// strings.ToLower akan mengubah unicode dan memecah paritas.
func asciiLower(s string) string {
	b := []byte(s)
	for i := 0; i < len(b); i++ {
		if b[i] >= 'A' && b[i] <= 'Z' {
			b[i] += 'a' - 'A'
		}
	}
	return string(b)
}
