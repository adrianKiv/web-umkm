-- User MySQL read-only khusus service rekomendasi (Azure Database for MySQL).
-- Jalankan di server `umkmpuiws`, database `cobaleaflet`, sebagai admin server.
-- Ganti <PASSWORD_KUAT> dengan hasil `openssl rand -base64 24` (simpan di secret
-- Container App, jangan di repo).
--
-- Kenapa read-only: kalau API key service bocor, penyerang tetap tidak bisa
-- mengubah/menghapus data skripsi. Blast radius = baca saja.

CREATE USER 'rekomendasi_ro'@'%' IDENTIFIED BY '<PASSWORD_KUAT>';

-- Hanya SELECT, hanya di database aplikasi. Tidak ada INSERT/UPDATE/DELETE/DDL.
GRANT SELECT ON cobaleaflet.* TO 'rekomendasi_ro'@'%';

FLUSH PRIVILEGES;

-- Verifikasi (harus muncul hanya SELECT):
SHOW GRANTS FOR 'rekomendasi_ro'@'%';
