package repo

import (
	"context"

	"umkm/rekomendasi/internal/cbf"
)

// KategoriKelompok = peta kategori -> kelompok dan sebaliknya.
type KategoriKelompok struct {
	KeKelompok map[int]int
	Anggota    map[int][]int
}

// PetaKategori mengambil relasi kategori-kelompok sekali jalan.
// Urutan anggota mengikuti nama_kategori ASC, sama seperti
// `Kategori::with('kelompok')->orderBy('nama_kategori')` di landing().
func (s *Store) PetaKategori(ctx context.Context) (KategoriKelompok, error) {
	return s.peta.Get(ctx, s.loadPetaKategori)
}

func (s *Store) loadPetaKategori(ctx context.Context) (KategoriKelompok, error) {
	const query = `
		SELECT id_kategori, id_kelompok
		FROM kategori
		ORDER BY nama_kategori ASC`

	rows, err := s.db.QueryContext(ctx, query)
	if err != nil {
		return KategoriKelompok{}, err
	}
	defer rows.Close()

	out := KategoriKelompok{KeKelompok: map[int]int{}, Anggota: map[int][]int{}}
	for rows.Next() {
		var kategoriID, kelompokID int
		if err := rows.Scan(&kategoriID, &kelompokID); err != nil {
			return KategoriKelompok{}, err
		}
		out.KeKelompok[kategoriID] = kelompokID
		out.Anggota[kelompokID] = append(out.Anggota[kelompokID], kategoriID)
	}

	return out, rows.Err()
}

// UmkmRingkas mengambil semua kandidat + rata-rata rating dalam SATU query.
// Ini pengganti pola `$umkm->rating->avg()` yang di Eloquent memicu lazy load
// per baris (N+1).
func (s *Store) UmkmRingkas(ctx context.Context) ([]cbf.Umkm, error) {
	return s.umkm.Get(ctx, s.loadUmkmRingkas)
}

func (s *Store) loadUmkmRingkas(ctx context.Context) ([]cbf.Umkm, error) {
	const query = `
		SELECT u.id_umkm,
		       u.nama_umkm,
		       u.id_kategori,
		       COALESCE(AVG(r.nilai_rating), 0) AS rating_avg
		FROM umkm u
		LEFT JOIN rating r ON r.id_umkm = u.id_umkm
		GROUP BY u.id_umkm, u.nama_umkm, u.id_kategori
		ORDER BY u.nama_umkm ASC, u.id_umkm ASC`

	rows, err := s.db.QueryContext(ctx, query)
	if err != nil {
		return nil, err
	}
	defer rows.Close()

	var out []cbf.Umkm
	for rows.Next() {
		var u cbf.Umkm
		if err := rows.Scan(&u.ID, &u.Nama, &u.KategoriID, &u.RatingAvg); err != nil {
			return nil, err
		}
		out = append(out, u)
	}

	return out, rows.Err()
}

// ImplicitCounts meniru blok landing():
//
//	->when($userId, fn($q) => $q->where('id_user', $userId),
//	                 fn($q) => $q->where('id_session', $sessionId))
//
// Artinya: user login difilter by id_user, tamu by id_session, tidak pernah
// keduanya sekaligus.
func (s *Store) ImplicitCounts(ctx context.Context, userID int, sessionID string) ([]cbf.KategoriCount, error) {
	const base = `
		SELECT id_kategori, COUNT(*) AS total
		FROM user_activities
		WHERE interaction_type = 'detail_click' AND `

	var (
		query string
		arg   any
	)
	if userID > 0 {
		query, arg = base+"id_user = ? GROUP BY id_kategori", userID
	} else {
		query, arg = base+"id_session = ? GROUP BY id_kategori", sessionID
	}

	rows, err := s.db.QueryContext(ctx, query, arg)
	if err != nil {
		return nil, err
	}
	defer rows.Close()

	out := []cbf.KategoriCount{}
	for rows.Next() {
		var (
			kategoriID *int // kolom nullable: PHP meng-cast NULL jadi 0
			total      int
		)
		if err := rows.Scan(&kategoriID, &total); err != nil {
			return nil, err
		}
		id := 0
		if kategoriID != nil {
			id = *kategoriID
		}
		out = append(out, cbf.KategoriCount{ID: id, Total: total})
	}

	return out, rows.Err()
}

// Rekomendasi = jalur lengkap: ambil bahan dari DB lalu hitung skor.
func (s *Store) Rekomendasi(ctx context.Context, explicit []int, userID int, sessionID string, params cbf.Params) (cbf.Hasil, error) {
	peta, err := s.PetaKategori(ctx)
	if err != nil {
		return cbf.Hasil{}, err
	}

	umkm, err := s.UmkmRingkas(ctx)
	if err != nil {
		return cbf.Hasil{}, err
	}

	var implicit []cbf.KategoriCount
	if userID > 0 || sessionID != "" {
		implicit, err = s.ImplicitCounts(ctx, userID, sessionID)
		if err != nil {
			return cbf.Hasil{}, err
		}
	}

	return cbf.Skor(cbf.Input{
		Explicit:         explicit,
		Implicit:         implicit,
		KategoriKelompok: peta.KeKelompok,
		KelompokMembers:  peta.Anggota,
		Umkm:             umkm,
		Params:           params,
	}), nil
}
