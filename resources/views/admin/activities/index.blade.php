@extends('admin.layout')

@section('title', 'ADMIN ACTIVITIES - UMKM Kuliner')

@section('admin-content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
    <div>
        <h2 class="mb-1">Log Activities</h2>
        <div class="text-muted">Riwayat interaksi pengguna di landing page.</div>
    </div>
    <div class="ms-md-3 mt-3 mt-md-0">
        <form action="{{ route('admin.activities.index') }}" method="GET" class="d-flex">
            <input type="search" name="q" value="{{ isset($q) ? $q : request('q') }}" class="form-control form-control-sm" placeholder="Cari nama/email/kategori/session/tipe">
            <button class="btn btn-sm btn-primary ms-2">Cari</button>
        </form>
    </div>
</div>

@if($activities->isEmpty())
    <div class="admin-card">
        <div class="p-4 text-center text-muted">
            <i class="fas fa-info-circle me-2"></i>Belum ada aktivitas pengguna.
        </div>
    </div>
@else
    <div class="admin-card">
        <div class="table-responsive admin-table-wrapper">
            <table class="table table-hover align-middle admin-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Pengguna</th>
                        <th>Kategori</th>
                        <th>Interaksi</th>
                        <th>Session</th>
                        <th>Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($activities as $activity)
                        <tr>
                            <td>{{ ($activities->currentPage() - 1) * $activities->perPage() + $loop->iteration }}</td>
                            <td>
                                <div class="fw-semibold">{{ $activity->user->name ?? 'Guest' }}</div>
                                <small class="text-muted">{{ $activity->user->email ?? 'Tanpa akun' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-primary">{{ $activity->kategori->nama_kategori ?? '-' }}</span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">{{ $activity->interaction_type }}</span>
                            </td>
                            <td>
                                <code class="text-muted" title="{{ $activity->id_session }}">
                                    {{ \Illuminate\Support\Str::limit($activity->id_session, 18) }}
                                </code>
                            </td>
                            <td><small>{{ $activity->created_at?->format('d M Y H:i') }}</small></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex flex-column flex-md-row justify-content-center align-items-center mt-3 mb-3">
        <div class="text-muted mb-2 mb-md-0">
            Menampilkan {{ $activities->firstItem() ?? 0 }} - {{ $activities->lastItem() ?? 0 }} dari {{ $activities->total() }} aktivitas
        </div>
        <div>
            {{ $activities->links('layouts.custom') }}
        </div>
    </div>
@endif
@endsection
