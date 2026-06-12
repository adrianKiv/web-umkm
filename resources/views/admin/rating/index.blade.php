@extends('admin.layout')

@section('title', 'ADMIN RATING - UMKM Kuliner')

@section('admin-content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>Daftar Ulasan/Rating</h4>
    </div>

    <div class="admin-card">
        <div class="admin-card-header d-flex justify-content-end">
            <form action="{{ route('admin.rating.index') }}" method="GET" class="m-0">
                <div class="input-group input-width-350">
                    <input type="text" name="search" class="form-control"
                        placeholder="Cari UMKM / pengulas / komentar / rating"
                        value="{{ isset($search) ? $search : request('search') }}">

                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-search"></i>
                    </button>

                    @if (request('search'))
                        <a href="{{ route('admin.rating.index') }}" class="btn btn-outline-danger" title="Reset">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="card-body">
            @if ($ratings->isEmpty())
                <div class="alert alert-info text-center py-5">
                    <i class="fas fa-info-circle me-2"></i>
                    @if (request('search'))
                        Hasil pencarian "<strong>{{ request('search') }}</strong>" tidak ditemukan.
                    @else
                        Belum ada rating/ulasan.
                    @endif
                </div>
            @else
                <div class="table-responsive admin-table-wrapper">
                    <table class="table table-hover align-middle admin-table">
                        <thead>
                            <tr>
                                <th class="w-5p">ID</th>
                                <th class="w-20p">UMKM</th>
                                <th class="w-15p">Rating</th>
                                <th class="w-20p">Pengulas</th>
                                <th class="w-20p">Preview Ulasan</th>
                                <th class="w-15p">Tanggal</th>
                                <th class="w-5p">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($ratings as $rating)
                                <tr>
                                    <td>{{ $rating->id_rating }}</td>
                                    <td>
                                        <a href="{{ route('admin.umkm.show', $rating->umkm) }}"
                                            title="{{ $rating->umkm->nama_umkm ?? '-' }}">
                                            {{ \Illuminate\Support\Str::limit($rating->umkm->nama_umkm ?? '-', 26) }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning text-dark">
                                            <i class="fas fa-star"></i> {{ $rating->nilai_rating }}/5
                                        </span>
                                    </td>
                                    <td>{{ $rating->nama_pengulas ?? 'Anonim' }}</td>
                                    <td>
                                        <small class="text-truncate d-block text-truncate-max-200">
                                            {{ \Illuminate\Support\Str::limit($rating->komentar ?? '-', 72) }}
                                        </small>
                                    </td>
                                    <td>
                                        <small>{{ $rating->created_at?->format('d M Y H:i') }}</small>
                                    </td>
                                    <td>
                                        <div class="admin-actions">
                                            <a href="{{ route('admin.rating.show', $rating) }}"
                                                class="btn btn-sm btn-info text-white btn-icon" title="Lihat">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <form action="{{ route('admin.rating.destroy', $rating) }}" method="POST"
                                                class="d-inline-flex" onsubmit="return confirm('Yakin ingin menghapus?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm btn-icon"
                                                    title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
        </div>
    </div>

    @if ($ratings->hasPages())
        <div class="d-flex justify-content-center">
            {{ $ratings->links() }}
        </div>
    @endif
    @endif
@endsection
