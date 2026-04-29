@extends('admin.layout')

@section('admin-content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Daftar UMKM</h2>
    <a href="{{ route('admin.umkm.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Tambah UMKM
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-4 pb-0 d-flex justify-content-end">
        <form action="{{ route('admin.umkm.index') }}" method="GET" class="m-0">
            <div class="input-group" style="width: 300px;">
                <input type="text" name="search" class="form-control" placeholder="Cari nama UMKM..." value="{{ request('search') }}">
                <button class="btn btn-primary" type="submit">
                    <i class="fas fa-search"></i>
                </button>
                @if(request('search'))
                    <a href="{{ route('admin.umkm.index') }}" class="btn btn-outline-danger" title="Reset Pencarian">
                        <i class="fas fa-times"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
    <div class="table-responsive mt-2">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Foto</th>
                    <th>Nama UMKM</th>
                    <th>Kategori</th>
                    <th>Jam Buka</th>
                    <th>Rating Avg</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($umkms as $key => $umkm)
                    <tr>
                        <td>{{ ($umkms->currentPage() - 1) * $umkms->perPage() + $loop->iteration }}</td>
                        <td>
                            <img src="{{ $umkm->foto_umkm_url }}" alt="Foto {{ $umkm->nama_umkm }}"
                                 style="width: 56px; height: 56px; object-fit: cover; border-radius: 8px; border: 1px solid #dee2e6;"
                                 onerror="this.onerror=null;this.src='{{ asset('images/default-umkm.svg') }}';">
                        </td>
                        <td>{{ $umkm->nama_umkm }}</td>
                        <td><span class="badge bg-primary">{{ $umkm->kategori->nama_kategori ?? '-' }}</span></td>
                        <td>{{ $umkm->jam_buka }}</td>
                        <td>
                            <i class="fas fa-star text-warning"></i>
                            {{ number_format($umkm->rating->avg('nilai_rating') ?? 0, 1) }}
                        </td>
                        <td>
                            <a href="{{ route('admin.umkm.show', $umkm) }}" class="btn btn-sm btn-info text-white" title="Lihat">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.umkm.edit', $umkm) }}" class="btn btn-sm btn-warning text-white" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.umkm.destroy', $umkm) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            @if(request('search'))
                                UMKM dengan kata kunci "<strong>{{ request('search') }}</strong>" tidak ditemukan.
                            @else
                                Tidak ada data UMKM.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<div class="d-flex flex-column flex-md-row justify-content-center align-items-center mt-3 mb-3">
    <div class="text-muted mb-2 mb-md-0">
        Menampilkan {{ $umkms->firstItem() ?? 0 }} - {{ $umkms->lastItem() ?? 0 }} dari {{ $umkms->total() }} UMKM
    </div>
    <div>
        {{ $umkms->appends(request()->query())->links('layouts.custom') }}
    </div>
</div>
@endsection
