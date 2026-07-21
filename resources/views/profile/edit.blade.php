@extends('profile.partials.profilelay')

@section('contentprofil')

    @if (session('status') === 'profile-updated')
        <div class="alert neo-alert-success alert-dismissible fade show mb-4" role="alert">
            <strong>SUKSES!</strong> Profil berhasil diperbarui.
            <button type="button" class="neo-btn-close" data-bs-dismiss="alert" aria-label="Close"><i class="fas fa-times"></i></button>
        </div>
    @endif

    @if (session('status') === 'password-updated')
        <div class="alert neo-alert-success alert-dismissible fade show mb-4" role="alert">
            <strong>SUKSES!</strong> Password berhasil diperbarui.
            <button type="button" class="neo-btn-close" data-bs-dismiss="alert" aria-label="Close"><i class="fas fa-times"></i></button>
        </div>
    @endif

    <div class="profile-header mb-4">
        <div>
            <span class="neo-eyebrow">Akun Pengguna</span>
            <h2 class="profile-title">PROFIL SAYA</h2>
            <p class="profile-subtitle fw-bold">Kelola identitas akun, keamanan, dan riwayat aktivitas Anda dalam satu halaman.</p>
        </div>
        <a href="{{ route('landing') }}" class="neo-btn-white">
            <i class="fas fa-arrow-left"></i> KEMBALI KE LANDING
        </a>
    </div>

    @php
        $roleLabel = $user->role?->nama_role ? ucwords(str_replace('_', ' ', $user->role->nama_role)) : 'Pengguna';
        $joinedAt = optional($user->created_at)->translatedFormat('d M Y') ?? '-';
        $lastUpdated = optional($user->updated_at)->translatedFormat('d M Y H:i') ?? '-';
    @endphp

    <div class="profile-page container-fluid py-2 pb-5">
        <div class="row g-4 align-items-start">

            <!-- KOLOM KIRI: SUMMARY & STATS -->
            <div class="col-12 col-xl-4">
                <div class="neo-card-shell profile-summary">
                    <!-- Banner Profil -->
                    <div class="profile-banner">
                        <div class="profile-avatar">
                            {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                        </div>
                        <div class="profile-banner__text">
                            <span class="neo-tag mb-2">Dashboard Profil</span>
                            <h3 class="mb-0 fw-black text-uppercase">{{ $user->name }}</h3>
                            <p class="mb-0 fw-bold">{{ $user->email }}</p>
                        </div>
                    </div>

                    <!-- Meta Grid -->
                    <div class="profile-meta-grid">
                        <div class="neo-stat-card">
                            <span class="meta-label">Role</span>
                            <span class="meta-value">{{ $roleLabel }}</span>
                        </div>
                        <div class="neo-stat-card">
                            <span class="meta-label">Bergabung</span>
                            <span class="meta-value">{{ $joinedAt }}</span>
                        </div>
                        <div class="neo-stat-card" style="grid-column: span 2;">
                            <span class="meta-label">Update terakhir</span>
                            <span class="meta-value">{{ $lastUpdated }}</span>
                        </div>
                    </div>

                    <!-- Stats Grid -->
                    <div class="profile-stats-grid">
                        <div class="neo-stat-card bg-yellow">
                            <div class="stat-label text-dark">Aktivitas Meliat UMKM</div>
                            <div class="stat-value">{{ $activityCount }}×</div>
                        </div>
                        <div class="neo-stat-card bg-blue">
                            <div class="stat-label text-dark">Kategori Favorit</div>
                            <div class="stat-value-small">
                                @if ($favoriteCategories->isNotEmpty())
                                    {{ $favoriteCategories->pluck('nama_kategori')->implode(' - ') }}
                                @else
                                    <span>Belum ada</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Aksi -->
                    <div class="profile-actions">
                        <a href="{{ route('data-umkm.map') }}" class="neo-btn-green w-100 text-center">
                            <i class="fas fa-map-marked-alt me-2"></i> BUKA PETA UMKM
                        </a>
                        <a href="{{ route('landing') }}" class="neo-btn-white w-100 text-center">
                            <i class="fas fa-store me-2"></i> JELAJAHI UMKM
                        </a>
                    </div>
                </div>
            </div>

            <!-- KOLOM KANAN: KONTEN UTAMA -->
            <div class="col-12 col-xl-8">

                <!-- Preferensi UMKM -->
                @if ($user->role?->nama_role === 'user' || !$user->role)
                    <div class="neo-card-shell mb-4">
                        <div class="section-head border-bottom border-dark border-3 pb-3">
                            <div>
                                <span class="neo-eyebrow bg-yellow">Preferensi</span>
                                <h3 class="section-title text-uppercase">Kategori Sering Dilihat</h3>
                            </div>
                            <span class="section-note fw-bold border border-dark p-1">Berdasarkan aktivitas detail UMKM</span>
                        </div>

                        @if ($favoriteCategories->isNotEmpty())
                            <div class="favorite-grid pt-3">
                                @foreach ($favoriteCategories as $item)
                                    <div class="neo-stat-card">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <strong class="text-uppercase">{{ $item['nama_kategori'] }}</strong>
                                            <span class="neo-badge-blue">{{ $item['total'] }}x</span>
                                        </div>
                                        <small class="fw-bold">{{ $item['nama_kelompok'] }}</small>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="neo-empty-state">
                                <i class="fas fa-chart-pie"></i>
                                <p class="mb-1 fw-black text-uppercase">Belum ada riwayat aktivitas.</p>
                                <small class="fw-bold">Buka detail UMKM atau gunakan peta agar preferensi Anda mulai terbentuk.</small>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Form Update Profil & Password -->
                <div class="row g-4 mb-4 align-items-start">
                    <div class="col-12 col-lg-6">
                        <div class="neo-card-shell">
                            <div class="p-4 p-lg-4">
                                @include('profile.partials.update-profile-information-form')
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6">
                        <div class="neo-card-shell">
                            <div class="p-4 p-lg-4">
                                @include('profile.partials.update-password-form')
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Aktivitas Terakhir -->
                <div class="neo-card-shell mb-4">
                    <div class="section-head border-bottom border-dark border-3 pb-3">
                        <div>
                            <span class="neo-eyebrow bg-blue">Aktivitas Terakhir</span>
                            <h3 class="section-title text-uppercase">Jejak Interaksi Terbaru</h3>
                        </div>
                        <span class="section-note fw-bold border border-dark p-1">5 data terakhir</span>
                    </div>

                    @if ($recentActivities->isNotEmpty())
                        <div class="activity-list pt-3">
                            @foreach ($recentActivities as $activity)
                                <div class="neo-activity-item">
                                    <div class="activity-icon">
                                        <i class="fas fa-location-dot"></i>
                                    </div>
                                    <div class="activity-body">
                                        <div class="d-flex flex-column flex-sm-row justify-content-between gap-1">
                                            <strong class="text-uppercase">{{ optional($activity->kategori)->nama_kategori ?? 'Kategori tidak ditemukan' }}</strong>
                                            <span class="neo-badge-white">{{ optional($activity->created_at)->translatedFormat('d M Y H:i') ?? '-' }}</span>
                                        </div>
                                        <div class="fw-bold mt-1">
                                            {{ optional(optional($activity->kategori)->kelompok)->nama_kelompok ?? 'Tanpa kelompok' }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="neo-empty-state">
                            <i class="fas fa-clock-rotate-left"></i>
                            <p class="mb-1 fw-black text-uppercase">Belum ada aktivitas tercatat.</p>
                            <small class="fw-bold">Riwayat akan muncul setelah Anda membuka detail UMKM atau menggunakan fitur peta.</small>
                        </div>
                    @endif
                </div>

                <!-- Delete Account -->
                <div class="neo-card-shell border-danger border-4">
                    <div class="p-4 p-lg-4">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>

            </div>
        </div>
    </div>

    @push('styles')
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;600;900&display=swap');

            /* GLOBAL FONT & HELPERS */
            .profile-page, .profile-header {
                font-family: 'Space Grotesk', sans-serif;
                color: #000;
            }
            .fw-black { font-weight: 900 !important; }
            .bg-yellow { background-color: #ffde59 !important; }
            .bg-blue { background-color: #38bdf8 !important; }

            /* ALERTS NEO */
            .neo-alert-success {
                background: #5ad641;
                border: 3px solid #000;
                border-radius: 0;
                color: #000;
                font-weight: 600;
                box-shadow: 4px 4px 0 #000;
                display: flex;
                align-items: center;
                justify-content: space-between;
            }
            .neo-btn-close {
                background: transparent;
                border: 2px solid #000;
                padding: 2px 8px;
                font-weight: 900;
                cursor: pointer;
            }
            .neo-btn-close:hover { background: #fff; }

            /* HEADER */
            .profile-header {
                display: flex;
                justify-content: space-between;
                align-items: flex-end;
                gap: 1rem;
                flex-wrap: wrap;
            }
            .neo-eyebrow {
                display: inline-block;
                background: #000;
                color: #fff;
                font-weight: 900;
                text-transform: uppercase;
                padding: 4px 10px;
                font-size: 0.8rem;
                margin-bottom: 8px;
                border: 2px solid #000;
            }
            .profile-title {
                margin: 0;
                font-weight: 900;
                font-size: 2.5rem;
                color: #000;
            }

            /* NEO CARD SHELL */
            .neo-card-shell {
                background: #fff;
                border: 4px solid #000;
                border-radius: 0;
                box-shadow: 8px 8px 0 #000;
                overflow: hidden;
                margin-bottom: 1.5rem;
            }
            .profile-summary {
                position: sticky;
                top: 1.2rem;
            }

            /* BANNER & AVATAR */
            .profile-banner {
                display: flex;
                gap: 1.2rem;
                align-items: center;
                padding: 1.5rem;
                background: #ffde59;
                border-bottom: 4px solid #000;
                color: #000;
            }
            .profile-avatar {
                width: 76px;
                height: 76px;
                border-radius: 0;
                border: 3px solid #000;
                background: #fff;
                color: #000;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 2rem;
                font-weight: 900;
                box-shadow: 4px 4px 0 #000;
                flex-shrink: 0;
            }
            .neo-tag {
                display: inline-block;
                border: 2px solid #000;
                background: #fff;
                font-weight: 900;
                font-size: 0.7rem;
                padding: 2px 8px;
                text-transform: uppercase;
            }

            /* GRIDS & CARDS */
            .profile-meta-grid, .profile-stats-grid, .favorite-grid, .activity-list {
                display: grid;
                gap: 1rem;
                padding: 1.25rem;
            }
            .profile-meta-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .favorite-grid { grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); }

            .neo-stat-card {
                background: #fff;
                border: 3px solid #000;
                padding: 1rem;
                box-shadow: 4px 4px 0 #000;
            }
            .meta-label, .stat-label {
                display: block;
                font-size: 0.8rem;
                text-transform: uppercase;
                font-weight: 900;
                color: #000;
                border-bottom: 2px solid #000;
                padding-bottom: 4px;
                margin-bottom: 8px;
            }
            .meta-value, .stat-value {
                font-weight: 900;
                font-size: 1.1rem;
                color: #000;
            }
            .stat-value { font-size: 2rem; }
            .stat-value-small { font-weight: 900; font-size: 1rem; line-height: 1.2; }

            /* BUTTONS NEO */
            .profile-actions {
                display: grid;
                gap: 1rem;
                padding: 0 1.25rem 1.25rem;
            }
            .neo-btn-green, .neo-btn-white, .neo-btn-blue {
                display: inline-block;
                border: 3px solid #000;
                padding: 0.8rem 1.2rem;
                font-weight: 900;
                text-transform: uppercase;
                text-decoration: none;
                color: #000;
                box-shadow: 4px 4px 0 #000;
                transition: transform 0.1s, box-shadow 0.1s;
                cursor: pointer;
            }
            .neo-btn-green { background: #5ad641; }
            .neo-btn-white { background: #fff; }
            .neo-btn-blue { background: #38bdf8; }

            .neo-btn-green:active, .neo-btn-white:active, .neo-btn-blue:active {
                transform: translate(4px, 4px);
                box-shadow: 0 0 0 #000;
            }
            .neo-btn-green:hover { background: #4ec237; color:#000; }
            .neo-btn-white:hover { background: #e0e0e0; color:#000; }

            /* SECTIONS & ACTIVITIES */
            .section-head {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                padding: 1.5rem 1.5rem 0;
            }
            .section-title { font-weight: 900; margin-top: 8px; }

            .neo-badge-blue, .neo-badge-white {
                border: 2px solid #000;
                padding: 2px 8px;
                font-weight: 900;
                font-size: 0.8rem;
            }
            .neo-badge-blue { background: #38bdf8; }
            .neo-badge-white { background: #fff; }

            .neo-activity-item {
                display: flex;
                gap: 1rem;
                padding: 1rem;
                border: 3px solid #000;
                background: #f4f4f2;
                box-shadow: 4px 4px 0 #000;
            }
            .activity-icon {
                width: 48px;
                height: 48px;
                border: 3px solid #000;
                background: #ffde59;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.2rem;
                flex-shrink: 0;
            }

            .neo-empty-state {
                padding: 2rem;
                text-align: center;
                background: #e0e0e0;
                border-top: 3px solid #000;
            }
            .neo-empty-state i {
                font-size: 2rem;
                margin-bottom: 1rem;
            }

            /* RESPONSIVE */
            @media (max-width: 1199.98px) {
                .profile-summary { position: relative; top: auto; }
            }
            @media (max-width: 768px) {
                .profile-header { flex-direction: column; align-items: flex-start; }
                .profile-meta-grid, .profile-stats-grid, .favorite-grid { grid-template-columns: 1fr; }
            }
        </style>
    @endpush
@endsection
