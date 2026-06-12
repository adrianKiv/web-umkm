@extends('admin.layout')

@section('title', 'ADMIN MENU - UMKM Kuliner')

@section('admin-content')
<div class="row">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Edit Menu UMKM</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.menu.update', $menu) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="id_umkm" class="form-label">UMKM <span class="text-danger">*</span></label>
                        <select class="form-select @error('id_umkm') is-invalid @enderror" id="id_umkm" name="id_umkm" required>
                            <option value="">-- Pilih UMKM --</option>
                            @foreach($umkms as $umkm)
                                <option value="{{ $umkm->id_umkm }}" {{ old('id_umkm', $menu->id_umkm) == $umkm->id_umkm ? 'selected' : '' }}>
                                    {{ $umkm->nama_umkm }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_umkm')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="nama_menu" class="form-label">Nama Menu <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nama_menu') is-invalid @enderror"
                               id="nama_menu" name="nama_menu" value="{{ old('nama_menu', $menu->nama_menu) }}" required>
                        @error('nama_menu')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="harga_menu" class="form-label">Harga Menu <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" class="form-control @error('harga_menu') is-invalid @enderror"
                               id="harga_menu" name="harga_menu" value="{{ old('harga_menu', $menu->harga_menu) }}" required>
                        @error('harga_menu')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="foto_menu" class="form-label">Foto Menu</label>
                        <input type="file" class="form-control @error('foto_menu') is-invalid @enderror"
                               id="foto_menu" name="foto_menu" accept="image/*">
                        <small class="text-muted d-block mb-2">Kosongkan jika tidak ingin mengganti foto.</small>
                            <img src="{{ $menu->foto_menu_url }}" alt="Foto {{ $menu->nama_menu }}"
                                class="thumb-120x90"
                                onerror="this.onerror=null;this.src='{{ asset('images/default-menu.svg') }}';">
                        @error('foto_menu')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Update
                        </button>
                        <a href="{{ route('admin.menu.show', $menu) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
