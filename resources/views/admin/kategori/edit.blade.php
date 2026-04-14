@extends('admin.layout')

@section('admin-content')
<div class="row">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Edit Kategori</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.kategori.update', $kategori) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="nama_kategori" class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nama_kategori') is-invalid @enderror"
                               id="nama_kategori" name="nama_kategori" value="{{ old('nama_kategori', $kategori->nama_kategori) }}" required>
                        @error('nama_kategori')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="slug_kategori" class="form-label">Slug</label>
                        <input type="text" class="form-control @error('slug_kategori') is-invalid @enderror"
                               id="slug_kategori" name="slug_kategori" value="{{ old('slug_kategori', $kategori->slug_kategori) }}">
                        @error('slug_kategori')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="id_kelompok" class="form-label">Kelompok <span class="text-danger">*</span></label>
                        <select class="form-select @error('id_kelompok') is-invalid @enderror"
                                id="id_kelompok" name="id_kelompok" required>
                            <option value="">-- Pilih Kelompok --</option>
                            @foreach($kelompoks as $kelompok)
                                <option value="{{ $kelompok->id_kelompok }}" {{ old('id_kelompok', $kategori->id_kelompok) == $kelompok->id_kelompok ? 'selected' : '' }}>
                                    {{ $kelompok->nama_kelompok }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_kelompok')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Update
                        </button>
                        <a href="{{ route('admin.kategori.show', $kategori) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
