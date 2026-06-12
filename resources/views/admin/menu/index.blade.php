@extends('admin.layout')

@section('title', 'ADMIN MENU - UMKM Kuliner')

@section('admin-content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Daftar Menu UMKM</h2>
        <a href="{{ route('admin.menu.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Tambah Menu
        </a>
    </div>

    <div class="admin-card">
        <div class="admin-card-header d-flex justify-content-end">
            <form action="{{ route('admin.menu.index') }}" method="GET" class="m-0">
                <div class="input-group input-width-320">
                    <input type="text" name="search" class="form-control" placeholder="Cari nama menu atau UMKM..."
                        value="{{ request('search') }}">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                    @if (request('search'))
                        <a href="{{ route('admin.menu.index') }}" class="btn btn-outline-danger" title="Reset Pencarian">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
        <div class="table-responsive admin-table-wrapper">
            <table class="table table-hover align-middle admin-table">
                <thead>
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
                            <td>{{ ($menus->currentPage() - 1) * $menus->perPage() + $loop->iteration }}</td>
                            <td>
                                <img src="{{ $menu->foto_menu_url }}" alt="Foto {{ $menu->nama_menu }}"
                                    class="thumb-52"
                                    onerror="this.onerror=null;this.src='{{ asset('images/default-menu.svg') }}';">
                            </td>
                            <td>
                                @if ($menu->is_foto_daftar_menu)
                                    <span class="badge bg-info">Foto daftar menu</span>
                                @else
                                    {{ $menu->nama_menu }}
                                @endif
                            </td>
                            <td>
                                @if ($menu->is_foto_daftar_menu)
                                    <span class="text-muted">-</span>
                                @else
                                    Rp{{ number_format((float) $menu->harga_menu, 0, ',', '.') }}
                                @endif
                            </td>
                            <td>
                                <span title="{{ $menu->umkm->nama_umkm ?? '-' }}">
                                    {{ \Illuminate\Support\Str::limit($menu->umkm->nama_umkm ?? '-', 26) }}
                                </span>
                            </td>
                            <td>
                                    <div class="admin-actions">
                                        <a href="{{ route('admin.menu.show', $menu) }}" class="btn btn-sm btn-info text-white btn-icon"
                                            title="Lihat">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.menu.edit', $menu) }}" class="btn btn-sm btn-warning text-white btn-icon"
                                            title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.menu.destroy', $menu) }}" method="POST" class="d-inline-flex"
                                            onsubmit="return confirm('Yakin ingin menghapus menu ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger btn-icon" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                @if (request('search'))
                                    Menu atau UMKM dengan kata kunci "<strong>{{ request('search') }}</strong>" tidak
                                    ditemukan.
                                @else
                                    Belum ada data menu.
                                @endif
                            </td>
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
