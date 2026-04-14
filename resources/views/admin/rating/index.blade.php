@extends('admin.layout')

@section('admin-content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Daftar Ulasan/Rating</h4>
</div>

@if($ratings->isEmpty())
    <div class="alert alert-info text-center py-5">
        <i class="fas fa-info-circle me-2"></i>Belum ada rating/ulasan
    </div>
@else
    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th style="width: 5%">ID</th>
                    <th style="width: 20%">UMKM</th>
                    <th style="width: 15%">Rating</th>
                    <th style="width: 20%">Pengulas</th>
                    <th style="width: 20%">Preview Ulasan</th>
                    <th style="width: 15%">Tanggal</th>
                    <th style="width: 5%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ratings as $rating)
                    <tr>
                        <td>{{ $rating->id_rating }}</td>
                        <td>
                            <a href="{{ route('admin.umkm.show', $rating->umkm) }}">
                                {{ $rating->umkm->nama_umkm }}
                            </a>
                        </td>
                        <td>
                            <span class="badge bg-warning text-dark">
                                <i class="fas fa-star"></i> {{ $rating->nilai_rating }}/5
                            </span>
                        </td>
                        <td>{{ $rating->nama_pengulas ?? 'Anonim' }}</td>
                        <td>
                            <small class="text-truncate d-block" style="max-width: 200px;">
                                {{ $rating->komentar ?? '-' }}
                            </small>
                        </td>
                        <td>
                            <small>{{ $rating->created_at?->format('d M Y H:i') }}</small>
                        </td>
                        <td>
                            <a href="{{ route('admin.rating.show', $rating) }}" class="btn btn-sm btn-info text-white" title="Lihat">
                                <i class="fas fa-eye"></i>
                            </a>
                            <form action="{{ route('admin.rating.destroy', $rating) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($ratings->hasPages())
        <div class="d-flex justify-content-center">
            {{ $ratings->links() }}
        </div>
    @endif
@endif
@endsection
