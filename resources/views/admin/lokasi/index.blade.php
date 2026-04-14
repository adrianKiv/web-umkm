@extends('admin.layout')

@section('admin-content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Daftar Lokasi</h4>
    <a href="{{ route('admin.lokasi.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Tambah Lokasi
    </a>
</div>

@if($lokasis->isEmpty())
    <div class="alert alert-info text-center py-5">
        <i class="fas fa-info-circle me-2"></i>Belum ada lokasi
    </div>
@else
    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th style="width: 5%">ID</th>
                    <th style="width: 25%">Nama Lokasi</th>
                    <th style="width: 25%">Koordinat</th>
                    <th style="width: 15%">UMKM</th>
                    <th style="width: 20%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lokasis as $lokasi)
                    <tr>
                        <td>{{ $lokasi->id_lokasi }}</td>
                        <td>{{ optional($lokasi->umkm)->alamat_lengkap ?? '-' }}</td>
                        <td>
                            <small class="font-monospace">
                                {{ number_format($lokasi->latitude, 4) }},
                                {{ number_format($lokasi->longitude, 4) }}
                            </small>
                        </td>
                        <td>{{ optional($lokasi->umkm)->nama_umkm ?? '-' }}</td>
                        <td>
                            <a href="{{ route('admin.lokasi.show', $lokasi) }}" class="btn btn-sm btn-info text-white" title="Lihat">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.lokasi.edit', $lokasi) }}" class="btn btn-sm btn-warning text-white" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.lokasi.destroy', $lokasi) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus?')">
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
        Menampilkan {{ $lokasis->firstItem() ?? 0 }} - {{ $lokasis->lastItem() ?? 0 }} dari {{ $lokasis->total() }} LOKASI
    </div>
    <div>
        {{ $lokasis->appends(request()->query())->links('layouts.custom') }}
    </div>
</div>
@endif
@endsection
