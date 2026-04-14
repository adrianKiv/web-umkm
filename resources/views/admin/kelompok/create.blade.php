@extends('admin.layout')

@section('admin-content')
<div class="row">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Tambah Kelompok</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.kelompok.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="nama_kelompok" class="form-label">Nama Kelompok <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nama_kelompok') is-invalid @enderror"
                               id="nama_kelompok" name="nama_kelompok" value="{{ old('nama_kelompok') }}" required>
                        @error('nama_kelompok')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Simpan
                        </button>
                        <a href="{{ route('admin.kelompok.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
