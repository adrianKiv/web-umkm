@extends('profile.partials.profilelay')

@section('contentprofil')

    @if (session('status') === 'profile-updated')
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Profil berhasil diperbarui.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('status') === 'password-updated')
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Password berhasil diperbarui.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="profile-header">
        <div>
            <p class="profile-eyebrow">Akun pengguna</p>
            <h2 class="profile-title">Profil Saya</h2>
            <p class="profile-subtitle">Kelola identitas akun, keamanan, dan riwayat aktivitas Anda dalam satu halaman.</p>
        </div>
        <a href="{{ route('landing') }}" class="profile-back-link">
            <i class="fas fa-arrow-left"></i>
            Kembali ke Landing
        </a>
    </div>

    @php
        $roleLabel = $user->role?->nama_role ? ucwords(str_replace('_', ' ', $user->role->nama_role)) : 'Pengguna';
        $joinedAt = optional($user->created_at)->translatedFormat('d M Y') ?? '-';
        $lastUpdated = optional($user->updated_at)->translatedFormat('d M Y H:i') ?? '-';
        $emailStatus = $user->hasVerifiedEmail() ? 'Terverifikasi' : 'Belum terverifikasi';
        $emailStatusClass = $user->hasVerifiedEmail() ? 'success' : 'warning';
    @endphp

    <div class="profile-page container-fluid py-4 py-lg-5">
        <div class="row g-4">
            <div class="col-12 col-xl-4">
                <div class="profile-summary card-shell">
                    <div class="profile-banner">
                        <div class="profile-avatar">
                            {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                        </div>
                        <div class="profile-banner__text">
                            <p class="mb-1 text-uppercase small fw-semibold opacity-75">Dashboard profil</p>
                            <h3 class="mb-1">{{ $user->name }}</h3>
                            <p class="mb-0 opacity-75">{{ $user->email }}</p>
                        </div>
                    </div>

                    <div class="profile-meta-grid">
                        <div class="meta-card">
                            <span class="meta-label">Role</span>
                            <span class="meta-value">{{ $roleLabel }}</span>
                        </div>
                        {{-- <div class="meta-card">
                            <span class="meta-label">Status Email</span>
                            <span class="badge text-bg-{{ $emailStatusClass }} rounded-pill">{{ $emailStatus }}</span>
                        </div> --}}
                        <div class="meta-card">
                            <span class="meta-label">Bergabung</span>
                            <span class="meta-value">{{ $joinedAt }}</span>
                        </div>
                        <div class="meta-card">
                            <span class="meta-label">Update terakhir</span>
                            <span class="meta-value">{{ $lastUpdated }}</span>
                        </div>
                    </div>

                    <div class="profile-stats-grid">
                        <div class="stat-card">
                            <div class="stat-label">Aktivitas</div>
                            <div class="stat-value">{{ $activityCount }}</div>
                        </div>
                        <div class="stat-card">
                            <!-- Menambahkan penyesuaian font agar teks yang panjang tidak terlalu raksasa -->
                            <div class="stat-label mt-2">Kategori favorit</div>
                            <div class="stat-value" style="font-size: 0.8rem; line-height: 1.1; white-space: normal;">
                                @if ($favoriteCategories->isNotEmpty())
                                    {{ $favoriteCategories->pluck('nama_kategori')->implode(' - ') }}
                                @else
                                    <span class="text-muted" style="font-size: 1rem;">Belum ada</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="profile-actions">
                        <a href="{{ route('data-umkm.map') }}" class="btn btn-primary w-100 rounded-pill">
                            <i class="fas fa-map-marked-alt me-2"></i>Buka Peta UMKM
                        </a>
                        <a href="{{ route('landing') }}" class="btn btn-outline-secondary w-100 rounded-pill">
                            <i class="fas fa-store me-2"></i>Jelajahi UMKM
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-8">
                @if ($user->role?->nama_role === 'user' || !$user->role)
                    <div class="card-shell mb-4">
                        <div class="section-head">
                            <div>
                                <p class="section-eyebrow">Preferensi</p>
                                <h3 class="section-title">Kategori yang sering Anda lihat</h3>
                            </div>
                            <span class="section-note">Berdasarkan aktivitas detail UMKM</span>
                        </div>

                        @if ($favoriteCategories->isNotEmpty())
                            <div class="favorite-grid">
                                @foreach ($favoriteCategories as $item)
                                    <div class="favorite-card">
                                        <div class="favorite-card__top">
                                            <span class="favorite-badge">{{ $item['total'] }}x</span>
                                            <strong>{{ $item['nama_kategori'] }}</strong>
                                        </div>
                                        <small class="text-muted">{{ $item['nama_kelompok'] }}</small>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-state">
                                <i class="fas fa-chart-pie"></i>
                                <p class="mb-1 fw-semibold">Belum ada riwayat aktivitas.</p>
                                <small class="text-muted">Buka detail UMKM atau gunakan peta agar preferensi Anda mulai
                                    terbentuk.</small>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Tambahkan align-items-start di sini -->
                <div class="row g-4 mb-4 align-items-start">
                    <div class="col-12 col-lg-6">
                        <!-- Hapus kelas h-100 di sini -->
                        <div class="card-shell">
                            <div class="p-4 p-lg-5">
                                @include('profile.partials.update-profile-information-form')
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6">
                        <!-- Hapus kelas h-100 di sini -->
                        <div class="card-shell">
                            <div class="p-4 p-lg-5">
                                @include('profile.partials.update-password-form')
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-shell mb-4">
                    <div class="section-head">
                        <div>
                            <p class="section-eyebrow">Aktivitas terakhir</p>
                            <h3 class="section-title">Jejak interaksi terbaru</h3>
                        </div>
                        <span class="section-note">5 data terakhir</span>
                    </div>

                    @if ($recentActivities->isNotEmpty())
                        <div class="activity-list">
                            @foreach ($recentActivities as $activity)
                                <div class="activity-item">
                                    <div class="activity-icon">
                                        <i class="fas fa-location-dot"></i>
                                    </div>
                                    <div class="activity-body">
                                        <div class="d-flex flex-column flex-sm-row justify-content-between gap-1">
                                            <strong>{{ optional($activity->kategori)->nama_kategori ?? 'Kategori tidak ditemukan' }}</strong>
                                            <small
                                                class="text-muted">{{ optional($activity->created_at)->translatedFormat('d M Y H:i') ?? '-' }}</small>
                                        </div>
                                        <div class="text-muted small">
                                            {{ optional(optional($activity->kategori)->kelompok)->nama_kelompok ?? 'Tanpa kelompok' }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-clock-rotate-left"></i>
                            <p class="mb-1 fw-semibold">Belum ada aktivitas tercatat.</p>
                            <small class="text-muted">Riwayat akan muncul setelah Anda membuka detail UMKM atau menggunakan
                                fitur peta.</small>
                        </div>
                    @endif
                </div>

                <div class="card-shell">
                    <div class="p-4 p-lg-5">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .profile-header {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                gap: 1rem;
                flex-wrap: wrap;
            }

            .profile-eyebrow,
            .section-eyebrow {
                margin: 0 0 0.25rem;
                text-transform: uppercase;
                letter-spacing: 0.12em;
                font-size: 0.72rem;
                font-weight: 700;
                color: #2563eb;
            }

            .profile-title,
            .section-title {
                margin: 0;
                font-weight: 800;
                color: #0f172a;
            }

            .profile-subtitle {
                margin: 0.35rem 0 0;
                color: #64748b;
                max-width: 52rem;
            }

            .profile-back-link {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                text-decoration: none;
                padding: 0.8rem 1rem;
                border-radius: 999px;
                border: 1px solid rgba(37, 99, 235, 0.18);
                background: rgba(37, 99, 235, 0.06);
                color: #1d4ed8;
                font-weight: 700;
                white-space: nowrap;
            }

            .profile-page {
                max-width: 1280px;
            }

            .card-shell {
                background: #ffffff;
                border: 1px solid rgba(148, 163, 184, 0.22);
                border-radius: 24px;
                box-shadow: 0 18px 50px rgba(15, 23, 42, 0.08);
                overflow: hidden;
            }

            .profile-summary {
                position: sticky;
                top: 1.2rem;
            }

            .profile-banner {
                display: flex;
                gap: 1rem;
                align-items: center;
                padding: 1.25rem;
                background: linear-gradient(135deg, #0f172a 0%, #1d4ed8 100%);
                color: #fff;
            }

            .profile-avatar {
                width: 72px;
                height: 72px;
                border-radius: 22px;
                background: rgba(255, 255, 255, 0.16);
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.8rem;
                font-weight: 800;
                flex-shrink: 0;
            }

            .profile-banner__text h3 {
                font-size: 1.4rem;
                margin: 0;
                font-weight: 800;
            }

            .profile-meta-grid,
            .profile-stats-grid,
            .favorite-grid {
                display: grid;
                gap: 0.85rem;
            }

            .profile-meta-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                padding: 1.1rem;
            }

            .meta-card,
            .stat-card,
            .favorite-card {
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                border-radius: 18px;
                padding: 0.95rem;
            }

            .meta-label,
            .stat-label {
                display: block;
                font-size: 0.75rem;
                text-transform: uppercase;
                letter-spacing: 0.08em;
                color: #64748b;
                margin-bottom: 0.25rem;
                font-weight: 700;
            }

            .meta-value,
            .stat-value {
                font-weight: 800;
                color: #0f172a;
            }

            .profile-stats-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                padding: 0 1.1rem 1.1rem;
            }

            .stat-card {
                text-align: center;
                background: linear-gradient(180deg, #f8fafc 0%, #eef6ff 100%);
            }

            .stat-value {
                font-size: 2rem;
            }

            .profile-actions {
                display: grid;
                gap: 0.75rem;
                padding: 0 1.1rem 1.1rem;
            }

            .section-head {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                gap: 1rem;
                flex-wrap: wrap;
                padding: 1.25rem 1.25rem 0;
                margin-bottom: 1rem;
            }

            .section-note {
                color: #64748b;
                font-size: 0.85rem;
            }

            .favorite-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
                padding: 0 1.25rem 1.25rem;
            }

            .favorite-card__top {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 0.75rem;
                margin-bottom: 0.35rem;
            }

            .favorite-badge {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-width: 42px;
                padding: 0.25rem 0.55rem;
                border-radius: 999px;
                background: #dbeafe;
                color: #1d4ed8;
                font-size: 0.8rem;
                font-weight: 800;
            }

            .activity-list {
                display: grid;
                gap: 0.75rem;
                padding: 0 1.25rem 1.25rem;
            }

            .activity-item {
                display: flex;
                gap: 0.9rem;
                padding: 0.95rem;
                border-radius: 18px;
                background: #f8fafc;
                border: 1px solid #e2e8f0;
            }

            .activity-icon {
                width: 42px;
                height: 42px;
                border-radius: 14px;
                background: linear-gradient(135deg, #dbeafe, #bfdbfe);
                color: #1d4ed8;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
            }

            .activity-body {
                flex: 1;
                min-width: 0;
            }

            .empty-state {
                padding: 1.4rem;
                text-align: center;
                color: #64748b;
            }

            .empty-state i {
                font-size: 1.5rem;
                color: #94a3b8;
                margin-bottom: 0.65rem;
            }

            @media (max-width: 1199.98px) {
                .profile-summary {
                    position: relative;
                    top: auto;
                }
            }

            @media (max-width: 768px) {
                .profile-header {
                    flex-direction: column;
                }

                .profile-back-link {
                    width: 100%;
                    justify-content: center;
                }

                .profile-meta-grid,
                .profile-stats-grid,
                .favorite-grid {
                    grid-template-columns: 1fr 1fr;
                }

                .favorite-grid {
                    grid-template-columns: 1fr;
                }

                .section-head {
                    padding-inline: 1rem;
                }

                .activity-list,
                .favorite-grid,
                .profile-actions {
                    padding-inline: 1rem;
                }
            }

            @media (max-width: 576px) {
                .profile-page {
                    padding-top: 0.75rem !important;
                }

                .profile-banner {
                    padding: 1rem;
                }

                .profile-avatar {
                    width: 58px;
                    height: 58px;
                    border-radius: 18px;
                    font-size: 1.35rem;
                }

                .profile-meta-grid,
                .profile-stats-grid {
                    grid-template-columns: 1fr;
                    padding-inline: 1rem;
                }

                .profile-actions {
                    padding-bottom: 1rem;
                }
            }
        </style>
    @endpush
@endsection
