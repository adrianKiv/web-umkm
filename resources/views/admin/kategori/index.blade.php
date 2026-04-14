@extends('admin.layout')

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
    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th style="width: 5%">ID</th>
                    <th style="width: 40%">Nama Kategori</th>
                    <th style="width: 20%">Kelompok</th>
                    <th style="width: 15%">Aksi</th>
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
                            <a href="{{ route('admin.kategori.show', $kategori) }}" class="btn btn-sm btn-info text-white" title="Lihat">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.kategori.edit', $kategori) }}" class="btn btn-sm btn-warning text-white" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.kategori.destroy', $kategori) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
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
