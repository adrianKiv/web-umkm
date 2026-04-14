@extends('admin.layout')

@section('admin-content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Daftar Pengguna</h4>
    <a href="{{ route('admin.user.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Tambah Pengguna
    </a>
</div>

@if($users->isEmpty())
    <div class="alert alert-info text-center py-5">
        <i class="fas fa-info-circle me-2"></i>Belum ada pengguna
    </div>
@else
    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th style="width: 5%">ID</th>
                    <th style="width: 25%">Nama</th>
                    <th style="width: 35%">Email</th>
                    <th style="width: 20%">Tanggal Dibuat</th>
                    <th style="width: 15%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
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
                            <a href="{{ route('admin.user.show', $user) }}" class="btn btn-sm btn-info text-white" title="Lihat">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.user.edit', $user) }}" class="btn btn-sm btn-warning text-white" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            @if(auth()->id() !== $user->id)
                                <form action="{{ route('admin.user.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
        <div class="d-flex justify-content-center">
            {{ $users->links() }}
        </div>
    @endif
@endif
@endsection
