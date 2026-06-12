@extends('admin.layout')

@section('title', 'ADMIN KELOMPOK - UMKM Kuliner')

@section('admin-content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Daftar Kelompok</h4>
    <a href="{{ route('admin.kelompok.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Tambah Kelompok
    </a>
</div>

@if($kelompoks->isEmpty())
    <div class="alert alert-info text-center py-5">
        <i class="fas fa-info-circle me-2"></i>Belum ada kelompok
    </div>
@else
    <div class="admin-card">
        <div class="table-responsive admin-table-wrapper">
            <table class="table table-hover align-middle admin-table">
            <thead>
                <tr>
                    <th class="w-5p">ID</th>
                    <th class="w-60p">Nama Kelompok</th>
                    <th class="w-15p">Kategori</th>
                    <th class="w-20p">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($kelompoks as $kelompok)
                    <tr>
                        <td>{{ $kelompok->id_kelompok }}</td>
                        <td>{{ $kelompok->nama_kelompok }}</td>
                        <td>
                            <span class="badge bg-secondary">{{ $kelompok->kategoris_count ?? 0 }}</span>
                        </td>
                        <td>
                            <div class="admin-actions">
                                <a href="{{ route('admin.kelompok.show', $kelompok) }}" class="btn btn-sm btn-info text-white btn-icon" title="Lihat">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.kelompok.edit', $kelompok) }}" class="btn btn-sm btn-warning text-white btn-icon" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.kelompok.destroy', $kelompok) }}" method="POST" class="d-inline-flex" onsubmit="return confirm('Yakin ingin menghapus?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Hapus">
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
    </div>

<div class="d-flex flex-column flex-md-row justify-content-center align-items-center mt-3 mb-3">
    <div class="text-muted mb-2 mb-md-0">
        Menampilkan {{ $kelompoks->firstItem() ?? 0 }} - {{ $kelompoks->lastItem() ?? 0 }} dari {{ $kelompoks->total() }} KELOMPOK
    </div>
    <div>
        {{ $kelompoks->appends(request()->query())->links('layouts.custom') }}
    </div>
</div>
@endif
@endsection
