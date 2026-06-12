@extends('admin.layout')

@section('title', 'ADMIN KATEGORI - UMKM Kuliner')

@section('admin-content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Daftar Kategori</h4>
    <a href="{{ route('admin.kategori.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Tambah Kategori
    </a>
</div>

@if($kategoris->isEmpty())
    <div class="alert alert-info text-center py-5">
        <i class="fas fa-info-circle me-2"></i>Belum ada kategori
    </div>
@else
    <div class="admin-card">
        <div class="table-responsive admin-table-wrapper">
            <table class="table table-hover align-middle admin-table">
            <thead>
                <tr>
                    <th class="w-5p">ID</th>
                    <th class="w-40p">Nama Kategori</th>
                    <th class="w-20p">Kelompok</th>
                    <th class="w-15p">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($kategoris as $kategori)
                    <tr>
                        <td>{{ $kategori->id_kategori }}</td>
                        <td>{{ $kategori->nama_kategori }}</td>
                        <td>
                            @if($kategori->kelompok)
                                <span class="badge bg-secondary">{{ $kategori->kelompok->nama_kelompok }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <div class="admin-actions">
                                <a href="{{ route('admin.kategori.show', $kategori) }}" class="btn btn-sm btn-info text-white btn-icon" title="Lihat">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.kategori.edit', $kategori) }}" class="btn btn-sm btn-warning text-white btn-icon" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.kategori.destroy', $kategori) }}" method="POST" class="d-inline-flex" onsubmit="return confirm('Yakin ingin menghapus?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger btn-icon" title="Hapus">
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
        Menampilkan {{ $kategoris->firstItem() ?? 0 }} - {{ $kategoris->lastItem() ?? 0 }} dari {{ $kategoris->total() }} KATEGORI
    </div>
    <div>
        {{ $kategoris->appends(request()->query())->links('layouts.custom') }}
    </div>
</div>
@endif
@endsection
