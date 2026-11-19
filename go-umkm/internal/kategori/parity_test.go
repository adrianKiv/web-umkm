package kategori

import (
	"encoding/json"
	"os"
	"strconv"
	"strings"
	"testing"
)

// goldenRow = isi parity/golden_php.json, dihasilkan langsung oleh PHP:
//
//	php parity/php_fixture.php parity/samples.json > parity/golden_php.json
//
// Isinya output asli OsmController::getKategoriId() + Str::slug().
type goldenRow struct {
	Nama     string `json:"nama"`
	Amenity  string `json:"amenity"`
	Kategori int    `json:"kategori"`
	Slug     string `json:"slug"`
}

// TestParityDenganPHP memastikan port Go tetap identik dengan output PHP asli.
// Kalau test ini merah: jangan ubah golden file, perbaiki Go-nya.
func TestParityDenganPHP(t *testing.T) {
	raw, err := os.ReadFile("../../parity/golden_php.json")
	if err != nil {
		t.Fatalf("golden fixture tidak terbaca: %v", err)
	}

	var rows []goldenRow
	if err := json.Unmarshal(raw, &rows); err != nil {
		t.Fatalf("golden fixture rusak: %v", err)
	}
	if len(rows) == 0 {
		t.Fatal("golden fixture kosong")
	}

	for _, row := range rows {
		// osmid diambil dari slug golden (format: <slug>-<osmid>).
		osmID, err := idDariSlug(row.Slug)
		if err != nil {
			t.Fatalf("slug %q tidak berakhiran id: %v", row.Slug, err)
		}

		if got := Map(row.Amenity, row.Nama); got != row.Kategori {
			t.Errorf("Map(%q, %q) = %d, PHP = %d", row.Amenity, row.Nama, got, row.Kategori)
		}
		if got := SlugWithID(row.Nama, osmID); got != row.Slug {
			t.Errorf("SlugWithID(%q, %d) = %q, PHP = %q", row.Nama, osmID, got, row.Slug)
		}
	}

	t.Logf("%d baris paritas identik dengan PHP", len(rows))
}

// idDariSlug mengambil osm_id di ujung slug "<slug>-<osm_id>".
func idDariSlug(slug string) (int64, error) {
	i := strings.LastIndex(slug, "-")
	if i < 0 || i == len(slug)-1 {
		return 0, strconv.ErrSyntax
	}
	return strconv.ParseInt(slug[i+1:], 10, 64)
}
