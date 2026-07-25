@extends('admin.layout')

@section('title', 'ADMIN USER - UMKM Kuliner')

@section('admin-content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>Daftar Pengguna</h4>
        <a href="{{ route('admin.user.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Tambah Pengguna
        </a>
    </div>

    @if ($users->isEmpty())
        <div class="alert alert-info text-center py-5">
            <i class="fas fa-info-circle me-2"></i>Belum ada pengguna
        </div>
    @else
        <div class="admin-card">
            <div class="admin-card-header d-flex justify-content-end">
                <form action="{{ route('admin.user.index') }}" method="GET" class="m-0">
                    <div class="input-group input-width-300">
                        <input type="text" name="search" class="form-control"
                            placeholder="Cari nama Pengguna atau email..." value="{{ request('search') }}">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                        @if(request('search'))
                            <a href="{{ route('admin.user.index') }}" class="btn btn-outline-danger" title="Reset Pencarian">
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
                            <th class="w-5p">ID</th>
                            <th class="w-25p">Nama</th>
                            <th class="w-35p">Email</th>
                            <th class="w-15p">Role</th>
                            <th class="w-20p">Tanggal Dibuat</th>
                            <th class="w-15p">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>{{ $user->name }}</td>
                                <td>
                                    <a href="mailto:{{ $user->email }}">{{ $user->email }}</a>
                                </td>
                                <td>
                                    <small>{{ $user->created_at?->format('d M Y H:i') }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ $user->role->nama_role }}</span>
                                </td>
                                <td>
                                    <div class="admin-actions">
                                        <a href="{{ route('admin.user.show', $user) }}"
                                            class="btn btn-sm btn-info text-white btn-icon" title="Lihat">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.user.edit', $user) }}"
                                            class="btn btn-sm btn-warning text-white btn-icon" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if (Auth::id() !== $user->id)
                                            @if (Auth()->user()->isSuperAdmin() || (Auth()->user()->isAdmin()))
                                                <form action="{{ route('admin.user.destroy', $user) }}" method="POST"
                                                    class="d-inline-flex"
                                                    onsubmit="return confirm('Yakin ingin menghapus pengguna ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger btn-icon">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if ($users->hasPages())
            <div class="d-flex justify-content-center">
                {{ $users->links() }}
            </div>
        @endif
    @endif
@endsection
