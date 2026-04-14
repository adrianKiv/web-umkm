@extends('admin.layout')

@section('admin-content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Daftar Menu UMKM</h2>
    <a href="{{ route('admin.menu.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Tambah Menu
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Foto</th>
                    <th>Nama Menu</th>
                    <th>Harga</th>
                    <th>UMKM</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($menus as $key => $menu)
                    <tr>
                        <td>{{ $menus->firstItem() + $key }}</td>
                        <td>
                            <img src="{{ $menu->foto_menu_url }}" alt="Foto {{ $menu->nama_menu }}"
                                 style="width: 52px; height: 52px; object-fit: cover; border-radius: 8px; border: 1px solid #dee2e6;"
                                 onerror="this.onerror=null;this.src='{{ asset('images/default-menu.svg') }}';">
                        </td>
                        <td>{{ $menu->nama_menu }}</td>
                        <td>Rp{{ number_format((float) $menu->harga_menu, 0, ',', '.') }}</td>
                        <td>{{ $menu->umkm->nama_umkm ?? '-' }}</td>
                        <td>
                            <a href="{{ route('admin.menu.show', $menu) }}" class="btn btn-sm btn-info text-white" title="Lihat">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.menu.edit', $menu) }}" class="btn btn-sm btn-warning text-white" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.menu.destroy', $menu) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Yakin ingin menghapus menu ini?')">
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
                        <td colspan="6" class="text-center text-muted py-4">Belum ada data menu.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="d-flex flex-column flex-md-row justify-content-center align-items-center mt-3 mb-3">
    <div class="text-muted mb-2 mb-md-0">
        Menampilkan {{ $menus->firstItem() ?? 0 }} - {{ $menus->lastItem() ?? 0 }} dari {{ $menus->total() }} menu
    </div>
    <div>
        {{ $menus->appends(request()->query())->links('layouts.custom') }}
    </div>
</div>
@endsection
