@extends('admin.layout')

@section('title', 'ADMIN LOKASI - UMKM Kuliner')

@section('admin-content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>Daftar Lokasi</h4>
        <a href="{{ route('admin.lokasi.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Tambah Lokasi
        </a>
    </div>

    <div class="admin-card">
        <div class="admin-card-header d-flex justify-content-end">
            <form action="{{ route('admin.lokasi.index') }}" method="GET" class="m-0">
                <div class="input-group input-width-350">
                    <input type="text" name="search" class="form-control"
                        placeholder="Cari alamat, UMKM, atau koordinat..." value="{{ request('search') }}">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                    @if (request('search'))
                        <a href="{{ route('admin.lokasi.index') }}" class="btn btn-outline-danger" title="Reset">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="card-body">
            @if ($lokasis->isEmpty())
                <div class="alert alert-info text-center py-5">
                    <i class="fas fa-info-circle me-2"></i>
                    @if (request('search'))
                        Hasil pencarian "<strong>{{ request('search') }}</strong>" tidak ditemukan.
                    @else
                        Belum ada lokasi.
                    @endif
                </div>
            @else
                <div class="table-responsive admin-table-wrapper">
                    <table class="table table-hover align-middle admin-table">
                        <thead>
                            <tr>
                                <th class="w-5p">No</th>
                                <th class="w-30p">Nama Lokasi (Alamat)</th>
                                <th class="w-25p">Koordinat</th>
                                <th class="w-20p">UMKM Terkait</th>
                                <th class="w-20p">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($lokasis as $key => $lokasi)
                                <tr>
                                    <td>{{ ($lokasis->currentPage() - 1) * $lokasis->perPage() + $loop->iteration }}</td>
                                    <td>
                                        <div class="text-truncate text-truncate-max-250"
                                            title="{{ optional($lokasi->umkm)->alamat_lengkap }}">
                                            {{ \Illuminate\Support\Str::limit(optional($lokasi->umkm)->alamat_lengkap ?? '-', 80) }}
                                        </div>
                                    </td>
                                    <td>
                                        <code class="text-primary bg-light px-2 py-1 rounded code-small">
                                            {{ number_format($lokasi->latitude, 6) }},
                                            {{ number_format($lokasi->longitude, 6) }}
                                        </code>
                                    </td>
                                    <td>
                                        @if ($lokasi->umkm)
                                            <span class="fw-bold text-dark" title="{{ $lokasi->umkm->nama_umkm }}">
                                                {{ \Illuminate\Support\Str::limit($lokasi->umkm->nama_umkm, 26) }}
                                            </span>
                                        @else
                                            <span class="text-muted italic small">Belum terhubung</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="admin-actions">
                                            <a href="{{ route('admin.lokasi.show', $lokasi) }}"
                                                class="btn btn-sm btn-info text-white btn-icon">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.lokasi.edit', $lokasi) }}"
                                                class="btn btn-sm btn-warning text-white btn-icon">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.lokasi.destroy', $lokasi) }}" method="POST"
                                                class="d-inline-flex" onsubmit="return confirm('Yakin ingin menghapus lokasi ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger btn-icon">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>

                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $lokasis->links() }}
                </div>
            @endif
        </div>
    </div>

    <div class="d-flex flex-column flex-md-row justify-content-center align-items-center mt-3 mb-3">
        <div class="text-muted mb-2 mb-md-0">
            Menampilkan {{ $lokasis->firstItem() ?? 0 }} - {{ $lokasis->lastItem() ?? 0 }} dari {{ $lokasis->total() }}
            LOKASI
        </div>
        <div>
            {{ $lokasis->appends(request()->query())->links('layouts.custom') }}
        </div>
    </div>
@endsection
