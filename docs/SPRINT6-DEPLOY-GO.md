# Sprint 6 — Deploy service Go ke Azure Container Apps

Tujuan: service rekomendasi hidup sebagai resource **terpisah** di subscription Azure yang
sama dengan Laravel, membaca `umkmpuiws` (MySQL Flexible Server) lewat TLS, dan dipanggil
Laravel `umkmpui` dengan header `X-API-Key`.

Kenapa bukan App Service kedua: Go di App Service berstatus *experimental* dan Free F1
dibatasi 60 menit CPU/hari. Kenapa bukan Render/Koyeb: service ini membaca MySQL Azure —
hosting di luar Azure berarti membuka firewall database ke IP pihak lain.

## Fakta yang sudah diverifikasi

| Hal | Status |
|---|---|
| Binary linux/amd64 | `CGO_ENABLED=0 GOOS=linux go build` → ELF 64-bit, 7,36 MB, go1.27.4 |
| Dockerfile | multi-stage, runtime `distroless/static:nonroot` (belum diuji `docker build` — Docker belum terpasang) |
| TLS ke MySQL | `DB_TLS=preferred` → `Ssl_cipher=TLS_AES_128_GCM_SHA256`, `Ssl_version=TLSv1.3` |
| Tanpa TLS | `Ssl_cipher` kosong → **akan ditolak Azure** (error 3159) |
| Grant minimal | `GRANT SELECT ON cobaleaflet.*` (lihat `deploy/mysql-readonly.sql`) |

## Prasyarat

1. `az` CLI terpasang (`winget install --id Microsoft.AzureCLI -e`).
2. `az login` dengan akun pemilik subscription.
3. Tahu **region** MySQL Flexible Server (portal → `umkmpuiws` → Overview → Location).
   Container App **wajib** di region yang sama agar latensi DB wajar.

```bash
az account show --query "{sub:name,id:id}" -o table
az mysql flexible-server show -g RG-WebGIS-KulinerUPI -n umkmpuiws \
   --query "{location:location,fqdn:fullyQualifiedDomainName,version:version}" -o table
```

## Langkah 1 — akses jaringan MySQL

Portal → `umkmpuiws` → **Networking**. Dua kemungkinan:

- **Public access** → tambahkan rule `0.0.0.0` (*Allow public access from any Azure service
  within Azure*). Ini yang paling sederhana; aman **hanya karena** TLS wajib + user read-only
  + password acak. Catat ini di skripsi sebagai trade-off, bukan disembunyikan.
- **Private access (VNet)** → Container App harus di-inject ke VNet yang sama
  (`az containerapp env create --infrastructure-subnet-resource-id ...`). Langkah lebih
  panjang; kalau ini kasusmu, jangan lanjut ke langkah 2 sebelum env-nya dibuat.

Cek juga parameter `require_secure_transport` (harus `ON`).

## Langkah 2 — buat Container App dari source (tanpa Docker lokal)

```bash
cd "D:/kuliah/semester 8 final act/UMKM-web/go-umkm"

az containerapp up \
  --name rekomendasi-go \
  --resource-group RG-WebGIS-KulinerUPI \
  --location <region-mysql> \
  --source . \
  --ingress external \
  --target-port 8081
```

Perintah ini membuat ACR, environment, dan build image **di Azure** (tidak butuh Docker di
laptop). Setelah selesai, catat FQDN yang dicetak.

## Langkah 3 — secret + environment

Jangan pernah menaruh password di `--set-env-vars` langsung (muncul di log/aktivitas).
Pakai secret:

```bash
az containerapp secret set -n rekomendasi-go -g RG-WebGIS-KulinerUPI --secrets \
  db-pass="<password rekomendasi_ro>" \
  api-key="$(openssl rand -hex 32)"

az containerapp update -n rekomendasi-go -g RG-WebGIS-KulinerUPI --set-env-vars \
  DB_HOST=umkmpuiws.mysql.database.azure.com \
  DB_PORT=3306 \
  DB_DATABASE=cobaleaflet \
  DB_USERNAME=rekomendasi_ro \
  DB_TLS=preferred \
  DB_PASSWORD=secretref:db-pass \
  REKOMENDASI_API_KEY=secretref:api-key \
  REKOMENDASI_CACHE_TTL=60
```

`DB_TLS=preferred` wajib: tanpa itu koneksi ditolak Azure. Default lokal tetap tanpa TLS
supaya angka benchmark Sprint 5 tidak berubah.

## Langkah 4 — replika + probe

```bash
az containerapp update -n rekomendasi-go -g RG-WebGIS-KulinerUPI \
  --min-replicas 0 --max-replicas 2
```

- `min-replicas 0` = gratis (masuk grant 180.000 vCPU-detik/bulan), tapi ada cold start
  1–2 detik pada request pertama setelah idle.
- Saat pekan sidang: `--min-replicas 1` supaya demo tidak kena cold start
  (7 hari × 0,25 vCPU ≈ 84% grant, masih di bawah batas gratis).

Probe (butuh YAML; jalankan sekali lalu simpan sebagai `deploy/containerapp.yaml`):

```bash
az containerapp show -n rekomendasi-go -g RG-WebGIS-KulinerUPI -o yaml > deploy/containerapp.yaml
```

Tambahkan di container `rekomendasi-go`:

```yaml
probes:
  - type: Liveness
    httpGet: { path: /healthz, port: 8081 }
    initialDelaySeconds: 5
    periodSeconds: 30
  - type: Readiness
    httpGet: { path: /healthz, port: 8081 }
    periodSeconds: 10
```

`/healthz` melakukan ping DB, jadi replika yang tidak bisa mencapai MySQL tidak akan
menerima trafik.

## Langkah 5 — verifikasi (jangan lompat)

```bash
BASE=https://<fqdn-container-app>
KEY="<api-key yang sama dengan secret api-key>"

curl -s $BASE/healthz                      # {"status":"ok",...}
curl -s -o /dev/null -w "%{http_code}\n" $BASE/api/v1/rekomendasi -X POST   # 401 tanpa key
curl -s $BASE/api/v1/rekomendasi -X POST \
  -H "X-API-Key: $KEY" -H "Content-Type: application/json" \
  -d '{"explicit_categories":[1,7]}'       # 10 id_umkm, urutan sama dengan lokal
```

Lalu di App Service `umkmpui` (Configuration → Application settings):

1. `REKOMENDASI_ENGINE=shadow` + `REKOMENDASI_URL=https://<fqdn>` + `REKOMENDASI_API_KEY=<key>`
2. Buka landing, cek log Laravel: `cbf-paritas: sama {...}`.
3. Setelah beberapa hari tanpa diff → `REKOMENDASI_ENGINE=go`.

Rollback = set `REKOMENDASI_ENGINE=php`. Tidak ada perubahan skema, tidak ada data yang
ditulis service ini.

## Status eksekusi (19 Nov 2026)

| Langkah | Status |
|---|---|
| Login `az` (Azure for Students) | ✅ `adrianmulianto@gmail.com` |
| Provider `Microsoft.App`, `Microsoft.ContainerRegistry`, `Microsoft.OperationalInsights` | ✅ Registered |
| User `rekomendasi_ro` + `GRANT SELECT` | ✅ `GRANT USAGE ON *.*` + `GRANT SELECT ON kulinerupi_db.*` |
| Koneksi Go → MySQL produksi | ✅ TLS, `umkm=144 rating=4225 aktivitas=476` |
| Container Apps environment `cae-umkmupi` | ✅ `gentlehill-5baf77a7.indonesiacentral.azurecontainerapps.io` |
| Image di registry | ✅ GHCR publik `ghcr.io/adriankiv/umkm-rekomendasi` (tag `latest` + SHA), manifest anonim `HTTP 200` |
| Container App `rekomendasi-go` | ✅ `rekomendasi-go.gentlehill-5baf77a7.indonesiacentral.azurecontainerapps.io`, min 0 / max 2, 0,25 vCPU / 0,5 GiB |
| Verifikasi HTTP | ✅ `/healthz` → `{"status":"ok"}`; POST tanpa key → **401**; POST dengan key → **200**, 10 hasil |
| Hasil rekomendasi produksi | ✅ `top_category_ids [1,7,2]`, ids `[99,131,89,40,79,105,15,106,145,35]`, 144 kandidat |
| Latensi | server-side `lama_ms` 0,067–0,113 ms; end-to-end keep-alive dari rumah p50 **30 ms** (min 13 ms) |
| Cleanup | ✅ firewall `laptop-adrian-temp` dihapus (4 rule asli tetap), file password scratch dihapus |

Catatan region: ACR boleh dibuat di `indonesiacentral`, tapi `az acr build` gagal
(`registries/listBuildSourceUploadUrl` tidak mendukung region itu) → image dibangun
GitHub Actions dan ditaruh di GHCR (gratis, publik, tanpa PAT). Alternatif kalau CI
tidak bisa dipakai: ACR di `southeastasia` (±$5/bulan dari kredit Students).

Ambil ulang API key saat menyambungkan Laravel:

```bash
az containerapp secret list -g RG-WebGIS-KulinerUPI -n rekomendasi-go --show-values
```

## Biaya

| Pola | Perkiraan |
|---|---|
| `min-replicas 0`, ±5.000 request/bulan | 625 vCPU-s (0,35% grant) → **Rp0** |
| `min-replicas 1` 24/7 | 657.000 vCPU-s = 3,6× grant → keluar gratis |
| `min-replicas 1` hanya 7 hari | 151.200 vCPU-s (84% grant) → masih gratis |

Pantau di portal → Container App → Metrics, atau `az consumption usage list`.
