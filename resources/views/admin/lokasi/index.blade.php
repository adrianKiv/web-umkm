@extends('admin.layout')

@section('admin-content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>Daftar Lokasi</h4>
        <a href="{{ route('admin.lokasi.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Tambah Lokasi
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Daftar Lokasi</h5>

            <form action="{{ route('admin.lokasi.index') }}" method="GET" class="m-0">
                <div class="input-group" style="width: 350px;">
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
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 5%">No</th>
                                <th style="width: 30%">Nama Lokasi (Alamat)</th>
                                <th style="width: 25%">Koordinat</th>
                                <th style="width: 20%">UMKM Terkait</th>
                                <th style="width: 20%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($lokasis as $key => $lokasi)
                                <tr>
                                    <td>{{ ($lokasis->currentPage() - 1) * $lokasis->perPage() + $loop->iteration }}</td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 250px;"
                                            title="{{ optional($lokasi->umkm)->alamat_lengkap }}">
                                            {{ optional($lokasi->umkm)->alamat_lengkap ?? '-' }}
                                        </div>
                                    </td>
                                    <td>
                                        <code class="text-primary bg-light px-2 py-1 rounded" style="font-size: 0.85rem;">
                                            {{ number_format($lokasi->latitude, 6) }},
                                            {{ number_format($lokasi->longitude, 6) }}
                                        </code>
                                    </td>
                                    <td>
                                        @if ($lokasi->umkm)
                                            <span class="fw-bold text-dark">{{ $lokasi->umkm->nama_umkm }}</span>
                                        @else
                                            <span class="text-muted italic small">Belum terhubung</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.lokasi.show', $lokasi) }}"
                                            class="btn btn-sm btn-info text-white">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.lokasi.edit', $lokasi) }}"
                                            class="btn btn-sm btn-warning text-white">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.lokasi.destroy', $lokasi) }}" method="POST"
                                            class="d-inline" onsubmit="return confirm('Yakin ingin menghapus lokasi ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>

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
