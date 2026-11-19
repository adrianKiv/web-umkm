#!/usr/bin/env bash
# Uji paritas Sprint 0: PHP asli vs port Go.
# Jalankan dari folder go-umkm.
set -euo pipefail

php -d display_errors=stderr parity/php_fixture.php parity/samples.json > parity/php_out.json
go run ./cmd/parity parity/samples.json > parity/go_out.json

python - <<'PY'
import json
php = json.load(open('parity/php_out.json', encoding='utf-8'))
go = json.load(open('parity/go_out.json', encoding='utf-8'))
print(f'php={len(php)} go={len(go)}', end=' ')
if php == go:
    print('-> IDENTIK')
else:
    beda = [(a, b) for a, b in zip(php, go) if a != b]
    print(f'-> {len(beda)} BEDA')
    for a, b in beda[:5]:
        print('PHP', a)
        print('GO ', b)
    raise SystemExit(1)
PY
