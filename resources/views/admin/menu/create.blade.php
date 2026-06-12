@extends('admin.layout')

@section('title', 'ADMIN MENU - UMKM Kuliner')

@section('admin-content')
<div class="row">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Tambah Menu UMKM</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.menu.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label for="id_umkm" class="form-label">UMKM <span class="text-danger">*</span></label>
                        <select class="form-select @error('id_umkm') is-invalid @enderror" id="id_umkm" name="id_umkm" required>
                            <option value="">-- Pilih UMKM --</option>
                            @foreach($umkms as $umkm)
                                <option value="{{ $umkm->id_umkm }}" {{ old('id_umkm') == $umkm->id_umkm ? 'selected' : '' }}>
                                    {{ $umkm->nama_umkm }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_umkm')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                    </div>

                    @php
                        $oldMenuNames = old('menu_nama', ['']);
                        $oldMenuPrices = old('menu_harga', ['']);
                        $maxMenuRows = max(count($oldMenuNames), count($oldMenuPrices), 1);
                    @endphp

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0">Data Menu</label>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="addAdminMenuItem">
                                <i class="fas fa-plus me-1"></i>Tambah Menu
                            </button>
                        </div>

                        <div id="adminMenuList" class="d-grid gap-2">
                            @for ($i = 0; $i < $maxMenuRows; $i++)
                                <div class="border rounded-3 p-2 submission-menu-item" data-menu-item>
                                    <div class="row g-2 align-items-end">
                                        <div class="col-md-5">
                                            <label class="form-label small">Nama Menu</label>
                                            <input type="text" name="menu_nama[]" class="form-control form-control-sm"
                                                value="{{ $oldMenuNames[$i] ?? '' }}" placeholder="Contoh: Ayam Bakar">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small">Harga</label>
                                            <input type="number" step="0.01" min="0" name="menu_harga[]"
                                                class="form-control form-control-sm" value="{{ $oldMenuPrices[$i] ?? '' }}"
                                                placeholder="Contoh: 25000">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small">Foto Menu</label>
                                            <input type="file" name="menu_foto[]" class="form-control form-control-sm" accept="image/*">
                                        </div>
                                        <div class="col-md-1 d-grid">
                                            <button type="button" class="btn btn-sm btn-outline-danger" data-remove-menu-item title="Hapus menu">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endfor
                        </div>

                        @if ($errors->has('menu_nama') || $errors->has('menu_nama.*') || $errors->has('menu_harga') || $errors->has('menu_harga.*'))
                            <div class="text-danger small mt-2">
                                @foreach (array_merge($errors->get('menu_nama'), $errors->get('menu_nama.*'), $errors->get('menu_harga'), $errors->get('menu_harga.*')) as $messages)
                                    @foreach ((array) $messages as $message)
                                        <div>{{ $message }}</div>
                                    @endforeach
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Foto Daftar Menu (Opsional)</label>
                        <input type="file" name="menu_daftar_foto[]" class="form-control" accept="image/*" multiple>
                        <small class="text-muted">Unggah satu atau lebih foto daftar menu tanpa nama/harga menu.</small>
                        @if ($errors->has('menu_daftar_foto') || $errors->has('menu_daftar_foto.*'))
                            <div class="text-danger small mt-2">
                                @foreach (array_merge($errors->get('menu_daftar_foto'), $errors->get('menu_daftar_foto.*')) as $messages)
                                    @foreach ((array) $messages as $message)
                                        <div>{{ $message }}</div>
                                    @endforeach
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Simpan
                        </button>
                        <a href="{{ route('admin.menu.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @vite('resources/js/refactor/admin-menu-list.js')
@endpush
