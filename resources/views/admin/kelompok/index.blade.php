@extends('admin.layout')

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
    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th style="width: 5%">ID</th>
                    <th style="width: 60%">Nama Kelompok</th>
                    <th style="width: 15%">Kategori</th>
                    <th style="width: 20%">Aksi</th>
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
                            <a href="{{ route('admin.kelompok.show', $kelompok) }}" class="btn btn-sm btn-info text-white" title="Lihat">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.kelompok.edit', $kelompok) }}" class="btn btn-sm btn-warning text-white" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.kelompok.destroy', $kelompok) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
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
        Menampilkan {{ $kelompoks->firstItem() ?? 0 }} - {{ $kelompoks->lastItem() ?? 0 }} dari {{ $kelompoks->total() }} KELOMPOK
    </div>
    <div>
        {{ $kelompoks->appends(request()->query())->links('layouts.custom') }}
    </div>
</div>
@endif
@endsection
