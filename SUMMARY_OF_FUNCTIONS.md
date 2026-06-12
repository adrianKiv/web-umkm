# Ringkasan Fungsi Aktif

Dokumen ini hanya memuat fungsi dan method yang benar-benar ada di source saat ini. Jika nama fungsi berubah atau dihapus, maka penjelasannya juga tidak dicantumkan lagi.

## JavaScript

### `resources/js/map.js`

Link sumber: [resources/js/map.js](resources/js/map.js)

**Helper umum**
- `isMobileViewport()` - mendeteksi viewport mobile untuk mengubah perilaku UI.
- `normalizeText(text)` - menormalkan teks untuk pencarian dan filter.
- `toCategoryKey(categoryName)` - mengubah nama kategori menjadi key yang aman untuk filter.
- `parseJamToMinutes(value)` - mengubah string jam menjadi nilai menit.
- `isUmkmOpenNow(jamBukaText)` - mengecek apakah UMKM sedang buka berdasarkan teks jam buka.
- `generateStars(rating)` - membuat HTML bintang rating.
- `formatMenuPrice(value)` - memformat harga menu ke Rupiah.
- `formatReviewDate(rawDate)` - memformat tanggal ulasan ke format Indonesia.
- `formatDistance(meters)` - memformat jarak ke meter atau kilometer.
- `formatDuration(seconds)` - memformat durasi ke menit atau jam.

**Konfigurasi, marker, dan state**
- `setTrackingControlsVisible(isVisible)` - menampilkan atau menyembunyikan wrapper kontrol live tracking dan status floating.
- `createUmkmMarkerIcon(isHighlighted = false)` - membuat icon marker Leaflet untuk UMKM.
- `createUserMarkerIcon()` - membuat icon marker Leaflet untuk posisi user.
- `hasStoredLocationConsent()` - membaca consent lokasi dari localStorage.
- `storeLocationConsent()` - menyimpan consent lokasi ke localStorage.
- `readMapConfig()` - membaca konfigurasi peta dari elemen DOM `#mapPageConfig`.
- `resolveUmkmCoordinates(umkmDataItem)` - mengambil koordinat UMKM dari data utama atau relasi lokasi.
- `getUmkmDetailUrl(umkmId)` - membentuk URL untuk tracking klik/detail UMKM.
- `getUmkmTrackUrl(umkmId)` - membentuk URL untuk tracking aktivitas UMKM.
- `trackUmkmActivity(umkmId)` - mengirim request untuk mencatat aktivitas detail click.
- `trackUmkmClick(umkmId)` - memicu tracking klik lalu tracking aktivitas.

**Lightbox gambar**
- `resetLightboxTransform(imageEl, resetButton = null)` - mereset zoom dan posisi gambar.
- `updateLightboxTransform(imageEl, resetButton = null)` - menerapkan transform zoom/pan saat ini.
- `clampLightboxTranslation(imageEl, nextX, nextY)` - membatasi pergeseran gambar agar tetap dalam batas preview.
- `openImageLightbox(imageUrl, caption = 'Preview Gambar')` - membuka modal preview gambar dengan zoom dan gesture.

**Panel detail dan ulasan**
- `buildUlasanElement(ulasanList)` - membangun elemen DOM berisi daftar ulasan.
- `closeDetailPanel()` - menutup panel detail dan menjalankan cleanup bottom-sheet bila ada.
- `syncSearchFilterToggleState(isActive)` - menyamakan state tombol filter pencarian.
- `initCategoryChipsInteraction()` - mengaktifkan scroll/drag horizontal untuk chips kategori.
- `initMobileDetailBottomSheet(panel)` - mengubah panel detail menjadi bottom sheet saat mobile.
- `prepareDetailPanel(panel)` - menyiapkan panel detail dan membersihkan bottom-sheet sebelumnya.
- `toggleUlasan(containerId, triggerBtn = null)` - menampilkan atau menyembunyikan ulasan.
- `setResultInfo(visibleCount)` - memperbarui teks jumlah hasil yang tampil.
- `renderCategoryChips()` - merender chips kategori dari data UMKM.
- `renderGroupFilters()` - mengisi dropdown kelompok untuk filter.
- `syncFilterControls()` - menyamakan nilai filter desktop dan mobile.
- `applyMapFilters(focusMap = false)` - menerapkan filter pencarian, kategori, kelompok, rating, dan open-now ke marker.
- `createPopupElement(item)` - membuat isi popup marker.
- `showUmkmDetail(umkmId)` - membangun panel detail UMKM lengkap dan memanggil `trackUmkmClick()`.
- `resetStars()` - mereset tampilan bintang rating.
- `setStars(rating)` - mengisi tampilan bintang rating sesuai nilai.
- `showAlert(type, message)` - menampilkan alert sementara di layar.

**Live tracking**
- `ensureLocationPermissionModal()` - memastikan modal izin lokasi tersedia dan mengaitkan tombol konfirmasi.
- `setLiveTrackFloatingStatus(message, tone = 'muted')` - mengubah teks dan warna status floating live tracking.
- `fetchOsrmRoute(userPosition, destination)` - mengambil rute driving dari OSRM.
- `stopLiveTracking(showMessage = false)` - menghentikan watch geolocation, marker user, rute, dan marker tujuan.
- `startLiveTrackingTo(latitude, longitude, umkmName = 'UMKM', skipPermissionDialog = false)` - memulai pelacakan rute menuju UMKM.

**Modal rating dan menu**
- `openRatingModal(umkmId, umkmName)` - membuka modal rating dan mereset form.
- `openMenuSubmissionModal(umkmId, umkmName)` - membuka modal pengajuan menu baru.
- `initRatingFeature()` - memasang interaksi bintang rating dan submit form rating via fetch.

**Inisialisasi utama**
- `initMapFeature(config)` - membuat peta Leaflet, marker UMKM, search, filter, modal, dan semua event binding utama.

### `resources/js/location-picker.js`

Link sumber: [resources/js/location-picker.js](resources/js/location-picker.js)

- `toNumber(value, fallback)` - parse input angka dengan fallback.
- `formatLatLng(latitude, longitude)` - menampilkan koordinat dalam format yang mudah dibaca.
- `initLocationPicker(container)` - membuat widget pemilih lokasi berbasis peta.
- `bootLocationPickers()` - menginisialisasi semua picker lokasi yang ada di halaman.

### `resources/js/landing.js`

Link sumber: [resources/js/landing.js](resources/js/landing.js)

- `performLiveSearch(query, shouldScroll = false)` - menjalankan pencarian live pada landing page.
- `scrollToResults()` - menggulir halaman ke hasil pencarian.
- `attachPaginationHandlers()` - memasang handler pagination.
- `handlePaginationClick(e)` - menangani klik pagination dan memuat hasil baru.

## PHP: Controller dan Request

### `app/Http/Controllers/DataUmkmController.php`

Link sumber: [app/Http/Controllers/DataUmkmController.php](app/Http/Controllers/DataUmkmController.php)

- `landing(Request $request)` - menampilkan landing page, filter, rekomendasi, dan modal preferensi kategori.
- `index()` - mengembalikan daftar UMKM dalam bentuk JSON.
- `map(Request $request)` - menyiapkan data untuk halaman peta, termasuk UMKM, kategori, dan UMKM terpilih.
- `detail(Umkm $umkm)` - mengembalikan detail UMKM untuk modal landing dan meningkatkan hit/klik.
- `trackActivity(Request $request, Umkm $umkm)` - mencatat interaksi detail click ke `UserActivity`.
- `storePreference(Request $request)` - menyimpan maksimal 3 kategori favorit ke session.
- `create()` - mengambil data lokasi untuk form pembuatan UMKM.
- `store(Request $request)` - menyimpan UMKM baru.
- `show(Umkm $data_umkm)` - menampilkan satu UMKM dalam JSON.
- `edit(Umkm $data_umkm)` - menyiapkan data edit UMKM dalam JSON.
- `update(Request $request, Umkm $data_umkm)` - memperbarui data UMKM.
- `destroy(Umkm $data_umkm)` - menghapus UMKM.

### `app/Http/Controllers/PublicUmkmSubmissionController.php`

Link sumber: [app/Http/Controllers/PublicUmkmSubmissionController.php](app/Http/Controllers/PublicUmkmSubmissionController.php)

- `store(Request $request)` - menyimpan pengajuan UMKM baru dari publik.
- `storeMenu(Request $request)` - menyimpan pengajuan menu baru atau foto menu dari publik.

### `app/Http/Controllers/ProfileController.php`

Link sumber: [app/Http/Controllers/ProfileController.php](app/Http/Controllers/ProfileController.php)

- `edit(Request $request): View` - menampilkan form edit profil.
- `update(ProfileUpdateRequest $request): RedirectResponse` - memperbarui nama/email pengguna dan mereset verifikasi email jika email berubah.
- `destroy(Request $request): RedirectResponse` - menghapus akun pengguna setelah validasi password.

### `app/Http/Controllers/OsmController.php`

Link sumber: [app/Http/Controllers/OsmController.php](app/Http/Controllers/OsmController.php)

- `sinkronisasiOsm()` - menarik data OSM dan menyinkronkan ke database lokal.
- `getKategoriId($amenity, $nama)` - memetakan tag OSM ke ID kategori lokal.

### `app/Console/Commands/SyncOsmUmkm.php`

Link sumber: [app/Console/Commands/SyncOsmUmkm.php](app/Console/Commands/SyncOsmUmkm.php)

- `handle()` - menjalankan sinkronisasi OSM dari command line.
- `mapKategori($amenity, $nama)` - memetakan data OSM ke kategori aplikasi.

### `app/Http/Controllers/Auth/*`

Link sumber: [app/Http/Controllers/Auth](app/Http/Controllers/Auth)

- `AuthenticatedSessionController::create()` - menampilkan form login.
- `AuthenticatedSessionController::store(LoginRequest $request)` - memproses login.
- `AuthenticatedSessionController::destroy(Request $request)` - logout user.
- `RegisteredUserController::create()` - menampilkan form registrasi.
- `RegisteredUserController::store(Request $request)` - memproses registrasi user baru.
- `PasswordResetLinkController::create()` - menampilkan form reset password.
- `PasswordResetLinkController::store(Request $request)` - mengirim link reset password.
- `NewPasswordController::create(Request $request)` - menampilkan form password baru.
- `NewPasswordController::store(Request $request)` - menyimpan password baru.
- `ConfirmablePasswordController::show()` - menampilkan form konfirmasi password.
- `ConfirmablePasswordController::store(Request $request)` - memverifikasi password sebelum aksi sensitif.
- `EmailVerificationPromptController::__invoke(Request $request)` - menampilkan prompt verifikasi email.
- `EmailVerificationNotificationController::store(Request $request)` - mengirim ulang notifikasi verifikasi email.
- `VerifyEmailController::__invoke(EmailVerificationRequest $request)` - memproses verifikasi email.
- `PasswordController::update(Request $request)` - mengubah password user yang sedang login.

### `app/Http/Controllers/Admin/*`

Link sumber: [app/Http/Controllers/Admin](app/Http/Controllers/Admin)

- `AdminDashboardController` - `index()`, `approveSubmission()`, `rejectSubmission()`, `approveMenuSubmission()`, `rejectMenuSubmission()`.
- `KategoriAdminController` - `index()`, `create()`, `store()`, `show()`, `edit()`, `update()`, `destroy()`.
- `KelompokAdminController` - `index()`, `create()`, `store()`, `show()`, `edit()`, `update()`, `destroy()`.
- `LokasiAdminController` - `index()`, `create()`, `store()`, `show()`, `edit()`, `update()`, `destroy()`.
- `MenuAdminController` - `index()`, `create()`, `store()`, `show()`, `edit()`, `update()`, `destroy()`.
- `RatingAdminController` - `index()`, `show()`, `destroy()`.
- `UmkmAdminController` - `index()`, `create()`, `store()`, `show()`, `edit()`, `update()`, `destroy()`.
- `UserAdminController` - `index()`, `create()`, `store()`, `show()`, `edit()`, `update()`, `destroy()`.
- `UserActivityAdminController` - `index()` untuk menampilkan aktivitas pengguna.

### `app/Http/Requests/*`

Link sumber: [app/Http/Requests](app/Http/Requests)

- `ProfileUpdateRequest::rules()` - aturan validasi profil.
- `Auth/LoginRequest::authorize()` - otorisasi login request.
- `Auth/LoginRequest::rules()` - validasi login.
- `Auth/LoginRequest::authenticate()` - autentikasi user.
- `Auth/LoginRequest::ensureIsNotRateLimited()` - proteksi rate limit login.
- `Auth/LoginRequest::throttleKey()` - key throttle untuk rate limit.

## Model, utilitas, dan file pendukung

### `app/Models/*`

Link sumber: [app/Models](app/Models)

- `Umkm::lokasi()`, `kategori()`, `rating()`, `menu()`, `menuSubmissions()` - relasi data UMKM.
- `Umkm::getFotoUmkmUrlAttribute()` - accessor URL foto UMKM.
- `Menu::umkm()`, `isFotoDaftarMenu()`, `getIsFotoDaftarMenuAttribute()`, `getFotoMenuUrlAttribute()` - relasi dan accessor menu.
- `MenuSubmission::umkmSubmission()`, `umkm()`, `reviewer()`, `isFotoDaftarMenu()`, `getIsFotoDaftarMenuAttribute()`, `getFotoMenuUrlAttribute()` - relasi dan accessor pengajuan menu.
- `Rating::umkm()` - relasi rating ke UMKM.
- `Lokasi::umkm()` - relasi lokasi ke UMKM.
- `Kelompok::kategori()` - relasi kelompok ke kategori.
- `Kategori::kelompok()`, `umkm()` - relasi kategori.
- `UmkmSubmission::kategori()`, `reviewer()`, `menuSubmissions()` - relasi pengajuan UMKM.
- `User::casts()` - cast atribut user.

### `app/Support/WebpImageUploader.php`

Link sumber: [app/Support/WebpImageUploader.php](app/Support/WebpImageUploader.php)

- `store(UploadedFile $file, string $directory, string $prefix, int $quality = 78, string $disk = 'public'): string` - menyimpan gambar ke storage dan mengoptimalkan ke WebP.

### `app/Providers/AppServiceProvider.php`

Link sumber: [app/Providers/AppServiceProvider.php](app/Providers/AppServiceProvider.php)

- `register()` - registrasi service/container binding bila diperlukan.
- `boot()` - menjalankan bootstrap logic aplikasi.

### `database/seeders/*`

Link sumber: [database/seeders](database/seeders)

- `run()` pada setiap seeder - mengisi data awal untuk kategori, kelompok, lokasi, UMKM, menu, rating, dan data pendukung lain.

### `database/migrations/*`

Link sumber: [database/migrations](database/migrations)

- `up()` - membuat atau mengubah struktur tabel.
- `down()` - membatalkan perubahan migrasi.

### `database/factories/*`

Link sumber: [database/factories](database/factories)

- `definition()` - menghasilkan data dummy untuk testing.
- `UserFactory::unverified()` - menandai user sebagai belum terverifikasi.

### `tests/Pest.php`

Link sumber: [tests/Pest.php](tests/Pest.php)

- `something()` - helper test bootstrap yang ada di file ini.

### `resources/views/partials/umkm-submission-modal.blade.php`

Link sumber: [resources/views/partials/umkm-submission-modal.blade.php](resources/views/partials/umkm-submission-modal.blade.php)

- `bindRemoveAction(root)` - helper kecil untuk menghapus item file/input pada modal pengajuan UMKM.

## Catatan pembaruan

- Fungsi lama `setLiveTrackStatus()` sudah diganti menjadi `setLiveTrackFloatingStatus()`.
- Fungsi tracking detail dan aktivitas di `map.js` sekarang ikut terdokumentasi: `getUmkmDetailUrl()`, `getUmkmTrackUrl()`, `trackUmkmActivity()`, dan `trackUmkmClick()`.
- Method baru yang sebelumnya belum masuk ringkasan juga sudah ditambahkan: `DataUmkmController::detail()`, `trackActivity()`, `storePreference()`, `ProfileController` methods, `Auth` controller methods, dan `UserActivityAdminController::index()`.



# Ringkasan Spesifikasi & Fungsi Web Peta UMKM

Dokumen ini memberikan ringkasan teknis dan fungsional dari proyek Web Peta UMKM.

## Spesifikasi Proyek

Berikut adalah rincian teknologi, framework, dan library utama yang digunakan dalam pengembangan aplikasi ini.

### Backend
- **Framework**: Laravel 12
- **Manajemen Dependensi**: Composer
- **Paket Utama**:
    - `laravel/framework`: Kernel dari aplikasi Laravel.
    - `laravel/breeze`: Untuk otentikasi (login, registrasi, dll.).
    - `laravel/sanctum`: Untuk otentikasi API.
    - `spatie/laravel-permission`: Untuk manajemen role dan permission (Super Admin, Admin, User).
    - `guzzlehttp/guzzle`: Klien HTTP untuk berinteraksi dengan API eksternal seperti OpenStreetMap.

### Frontend
- **Build Tool**: Vite
- **Framework CSS**: Bootstrap 5.3
- **Manajemen Dependensi**: NPM
- **Library Utama**:
    - `bootstrap` & `@popperjs/core`: Komponen UI dan layouting.
    - `leaflet`: Library JavaScript untuk peta interaktif.
    - `axios`: Klien HTTP untuk request dari sisi klien (JavaScript).
    - `sweetalert2`: Untuk notifikasi dan dialog yang lebih menarik.

### Database
- **ORM**: Eloquent ORM (bagian dari Laravel)
- **Sistem Migrasi**: Laravel Migrations untuk manajemen skema database.
- **Sistem Seeder**: Laravel Seeder untuk mengisi data awal.

---

## Ringkasan Fungsi

Berikut adalah dekomposisi fungsionalitas utama yang ada di dalam aplikasi, dibagi berdasarkan peran pengguna dan fitur.

### A. Fitur Publik (Untuk Semua Pengguna)

1.  **Landing Page (Halaman Utama)**
    - **Deskripsi**: Halaman utama yang menampilkan daftar UMKM.
    - **Fitur**:
        - Menampilkan daftar UMKM secara acak dengan paginasi.
        - **Sistem Rekomendasi (Content-Based Filtering)**:
            - Menampilkan 6 UMKM yang direkomendasikan di bagian atas berdasarkan preferensi eksplisit (pilihan kategori favorit) dan implisit (riwayat klik detail UMKM).
            - Memberi *highlight* (lencana "Rekomendasi") pada semua UMKM yang kategorinya sesuai dengan skor rekomendasi pengguna.
        - **Pencarian**: Mencari UMKM berdasarkan nama, alamat, atau nama kategori.
        - **Filter**: Menyaring UMKM berdasarkan Kelompok, Kategori, dan Rating minimum.
        - **Modal Detail**: Menampilkan detail singkat UMKM (foto, nama, jam buka, kategori) saat kartu UMKM diklik, tanpa meninggalkan halaman.
        - **Modal Preferensi**: Pengguna baru akan disambut dengan modal untuk memilih hingga 3 kategori favorit untuk personalisasi rekomendasi.

2.  **Peta Interaktif (Map View)**
    - **Deskripsi**: Visualisasi lokasi UMKM dalam sebuah peta interaktif.
    - **Fitur**:
        - Menampilkan semua UMKM sebagai *marker* di peta.
        - **Highlight Rekomendasi**: Marker untuk UMKM yang direkomendasikan akan memiliki visual yang berbeda (spotlight).
        - **Detail Panel**: Saat marker diklik, panel di sisi kanan akan menampilkan informasi lengkap UMKM (profil, menu, rating, dll).
        - **Pelacakan Aktivitas**: Klik pada detail UMKM dari peta akan dicatat untuk meningkatkan akurasi sistem rekomendasi.
        - **Filter Kategori**: Memfilter marker di peta berdasarkan kategori UMKM.
        - **Link Langsung**: URL dapat menyertakan ID UMKM untuk langsung membuka detail UMKM tertentu saat halaman peta dimuat.

3.  **Pengajuan & Kontribusi Publik**
    - **Deskripsi**: Fitur bagi publik untuk berkontribusi data UMKM dan menu. Semua data yang masuk akan ditinjau oleh admin.
    - **Fitur**:
        - **Form Pengajuan UMKM Baru**: Mengajukan UMKM baru lengkap dengan data (nama, deskripsi, lokasi via peta, foto) dan daftar menu.
        - **Form Pengajuan Menu**: Mengajukan menu baru atau foto daftar menu untuk UMKM yang sudah ada.
        - **Validasi**: Validasi input yang ketat di sisi server.
        - **Upload Gambar**: Menggunakan konversi ke format WebP untuk optimasi.

4.  **Sistem Rating & Ulasan**
    - **Deskripsi**: Pengguna dapat memberikan rating dan ulasan untuk UMKM.
    - **Fitur**:
        - Memberikan rating bintang (1-5).
        - Menuliskan komentar/ulasan.
        - Nama pengulas bisa dikosongkan dan akan disimpan sebagai "Anonymous".

5.  **Sinkronisasi OpenStreetMap (OSM)**
    - **Deskripsi**: Fitur internal untuk mengambil data UMKM kuliner dari OpenStreetMap di sekitar area UPI Bandung.
    - **Fitur**:
        - Mengambil data dari Overpass API.
        - Memfilter data berdasarkan tag `amenity` (restaurant, cafe, dll).
        - Secara otomatis membuat data `Lokasi` dan `UMKM` baru.
        - Mencegah duplikasi data berdasarkan `osm_id`.
        - Memetakan `amenity` OSM ke `Kategori` yang ada di sistem.

### B. Fitur Pengguna Terautentikasi (User)

1.  **Manajemen Profil**
    - **Deskripsi**: Pengguna yang sudah login dapat mengelola informasi profil mereka.
    - **Fitur**:
        - Mengubah nama dan alamat email.
        - Mengubah password.
        - Menghapus akun.

2.  **Personalisasi Berbasis Akun**
    - **Deskripsi**: Sistem rekomendasi akan mengikat riwayat aktivitas ke akun pengguna.
    - **Fitur**: Rekomendasi yang diterima akan tetap konsisten meskipun pengguna berpindah perangkat atau browser, selama mereka login.

### C. Panel Admin (Untuk Role Admin & Super Admin)

1.  **Dashboard Admin**
    - **Deskripsi**: Halaman utama panel admin yang menampilkan ringkasan data dan moderasi.
    - **Fitur**:
        - Statistik jumlah UMKM, User, Kategori, dll.
        - **Moderasi Pengajuan**: Menampilkan daftar pengajuan UMKM dan Menu baru yang butuh persetujuan (`Approve`) atau penolakan (`Reject`).
        - **Log Aktivitas Terbaru**: Menampilkan log aktivitas pengguna terbaru.

2.  **Manajemen Data (CRUD)**
    - **Deskripsi**: Admin dapat mengelola semua data master dalam sistem melalui antarmuka yang terdedikasi.
    - **Fitur**:
        - **CRUD UMKM**: Tambah, lihat, edit, dan hapus data UMKM.
        - **CRUD Menu**: Tambah, lihat, edit, dan hapus data menu untuk setiap UMKM.
        - **CRUD Kategori & Kelompok**: Mengelola kategori UMKM dan mengelompokkannya.
        - **CRUD Lokasi**: Mengelola data geografis (latitude, longitude).
        - **CRUD User**: Mengelola akun pengguna, termasuk mengubah role (misal: dari User menjadi Admin).

3.  **Manajemen Ulasan & Aktivitas**
    - **Deskripsi**: Memantau dan mengelola interaksi pengguna.
    - **Fitur**:
        - **Lihat & Hapus Rating**: Melihat semua ulasan yang masuk dan menghapus ulasan yang tidak pantas.
        - **Log Aktivitas Pengguna**: Melihat riwayat interaksi pengguna dengan sistem (misal: `detail_click`).

4.  **Sistem Role & Permission**
    - **Deskripsi**: Aplikasi memiliki sistem hak akses berbasis peran.
    - **Fitur**:
        - **Super Admin**: Memiliki akses penuh ke semua fitur admin.
        - **Admin**: Memiliki akses ke sebagian besar fitur admin, dengan beberapa batasan (jika dikonfigurasi).
        - **User**: Peran standar untuk pengguna terdaftar, tidak memiliki akses ke panel admin.
