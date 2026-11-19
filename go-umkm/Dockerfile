# Image service rekomendasi CBF (Go). Build dari folder go-umkm:
#   docker build -t rekomendasi-go:dev .
#   docker run --rm -p 8081:8081 --env-file ../.env -e REKOMENDASI_API_KEY=rahasia rekomendasi-go:dev
#
# Catatan: server menolak bind non-loopback tanpa API key (httpapi.ValidasiBind),
# jadi di container API key WAJIB diisi.

FROM golang:1.27-alpine AS build
WORKDIR /src

# Lapisan dependency dulu supaya cache tidak bongkar ulang tiap ubah kode.
COPY go.mod go.sum ./
RUN go mod download

COPY . .
# CGO off -> binary statis, bisa jalan di image kosong. -trimpath + ldflags untuk ukuran.
RUN CGO_ENABLED=0 GOOS=linux go build -buildvcs=false -trimpath \
      -ldflags "-s -w" -o /out/server ./cmd/server

FROM gcr.io/distroless/static-debian12:nonroot
COPY --from=build /out/server /server
USER nonroot:nonroot
EXPOSE 8081
# 0.0.0.0 wajib: default 127.0.0.1 tidak terlihat dari luar container.
ENTRYPOINT ["/server", "-addr", "0.0.0.0:8081"]
