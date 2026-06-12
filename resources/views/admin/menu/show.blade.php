@extends('admin.layout')

@section('title', 'ADMIN MENU - UMKM Kuliner')

@section('admin-content')
<div class="row">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Detail Menu UMKM</h5>
                <small class="text-muted">ID: {{ $menu->id_menu }}</small>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-3 fw-semibold">Foto Menu:</div>
                    <div class="col-sm-9">
                            <img src="{{ $menu->foto_menu_url }}" alt="Foto {{ $menu->nama_menu }}"
                                class="thumb-220x140"
                                onerror="this.onerror=null;this.src='{{ asset('images/default-menu.svg') }}';">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3 fw-semibold">Nama Menu:</div>
                    <div class="col-sm-9">
                        @if($menu->is_foto_daftar_menu)
                            <span class="badge bg-info">Foto daftar menu</span>
                        @else
                            {{ $menu->nama_menu }}
                        @endif
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3 fw-semibold">Harga:</div>
                    <div class="col-sm-9">
                        @if($menu->is_foto_daftar_menu)
                            <span class="text-muted">-</span>
                        @else
                            Rp{{ number_format((float) $menu->harga_menu, 0, ',', '.') }}
                        @endif
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-sm-3 fw-semibold">UMKM:</div>
                    <div class="col-sm-9">
                        @if($menu->umkm)
                            <a href="{{ route('admin.umkm.show', $menu->umkm) }}" title="{{ $menu->umkm->nama_umkm }}">
                                {{ \Illuminate\Support\Str::limit($menu->umkm->nama_umkm, 32) }}
                            </a>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 mt-4">
                    <a href="{{ route('admin.menu.edit', $menu) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-1"></i>Edit
                    </a>
                    <a href="{{ route('admin.menu.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Kembali
                    </a>
                    <form action="{{ route('admin.menu.destroy', $menu) }}" method="POST" class="mt-3">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus menu ini?')">
                            <i class="fas fa-trash me-1"></i>Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
