@extends('admin.layout')

@section('admin-content')
<div class="row">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Detail Pengguna</h5>
                <small class="text-muted">ID: {{ $user->id }}</small>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-3 fw-semibold">Nama:</div>
                    <div class="col-sm-9">{{ $user->name }}</div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3 fw-semibold">Email:</div>
                    <div class="col-sm-9">
                        <a href="mailto:{{ $user->email }}">{{ $user->email }}</a>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3 fw-semibold">Email Terverifikasi:</div>
                    <div class="col-sm-9">
                        @if($user->email_verified_at)
                            <span class="badge bg-success">
                                <i class="fas fa-check me-1"></i>Ya - {{ $user->email_verified_at->format('d M Y') }}
                            </span>
                        @else
                            <span class="badge bg-warning">
                                <i class="fas fa-exclamation me-1"></i>Belum
                            </span>
                        @endif
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3 fw-semibold">Tanggal Dibuat:</div>
                    <div class="col-sm-9">{{ $user->created_at?->format('l, d M Y H:i') }}</div>
                </div>

                <div class="row mb-4">
                    <div class="col-sm-3 fw-semibold">Terakhir Diupdate:</div>
                    <div class="col-sm-9">{{ $user->updated_at?->format('l, d M Y H:i') }}</div>
                </div>

                <div class="alert alert-info mb-3">
                    <i class="fas fa-info-circle me-2"></i>
                    User ini merupakan admin dengan akses penuh ke panel administrasi.
                </div>

                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('admin.user.edit', $user) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-1"></i>Edit
                    </a>
                    <a href="{{ route('admin.user.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Kembali
                    </a>
                    @if(auth()->id() !== $user->id)
                        <form action="{{ route('admin.user.destroy', $user) }}" method="POST" class="mt-3">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus pengguna ini?')">
                                <i class="fas fa-trash me-1"></i>Hapus
                            </button>
                        </form>
                    @else
                        <div class="alert alert-warning mb-0">
                            Anda tidak bisa menghapus akun Anda sendiri
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
