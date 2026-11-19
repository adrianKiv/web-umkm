#!/usr/bin/env bash
# Uji paritas Sprint 2: skor CBF PHP (landing) vs Go (service).
# Jalankan dari folder go-umkm. Butuh MySQL hidup.
set -euo pipefail

php -d display_errors=stderr parity/make_scenarios.php > parity/cbf_scenarios.json
php -d display_errors=stderr parity/cbf_reference.php parity/cbf_scenarios.json > parity/cbf_php.json

UMKM_DB_TEST=1 go test ./internal/repo -run TestParitasCBF -v
