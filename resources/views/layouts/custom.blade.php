<style>
.neo-pagination {
    display: flex;
    flex-wrap: wrap;
    gap: 8px; /* Jarak antar kotak agar bayangan tidak saling tumpuk */
    list-style: none;
    padding: 0;
    margin: 0;
}

.neo-page-item {
    margin: 0;
}

.neo-page-link {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 40px; /* Ukuran kotak seragam */
    height: 40px;
    padding: 0.5rem 0.75rem;
    background-color: #fff;
    color: #000;
    font-weight: 900;
    text-decoration: none;
    border: 3px solid #000;
    box-shadow: 3px 3px 0 #000;
    transition: transform 0.1s, box-shadow 0.1s;
    border-radius: 0; /* Sudut tajam */
}

/* Efek Hover untuk angka yang bisa diklik */
.neo-page-item:not(.disabled) .neo-page-link:hover {
    transform: translate(-2px, -2px);
    box-shadow: 5px 5px 0 #000;
    background-color: #f8f9fa;
    color: #000;
}

/* Efek saat tombol ditekan */
.neo-page-item:not(.disabled) .neo-page-link:active {
    transform: translate(2px, 2px);
    box-shadow: 1px 1px 0 #000;
}

/* Gaya khusus untuk halaman aktif (saat ini) */
.neo-page-item.active .neo-page-link {
    background-color: #5ad641; /* Hijau neon, sesuaikan jika ingin kuning #ffde59 */
    color: #000;
    pointer-events: none; /* Mencegah klik berulang pada halaman aktif */
}

/* Gaya untuk tombol disabled (Mentok / Elipsis ...) */
.neo-page-item.disabled .neo-page-link {
    background-color: #e0e0e0;
    color: #757575;
    box-shadow: 3px 3px 0 #000;
    cursor: not-allowed;
    border-color: #000;
}
</style>

@if ($paginator->hasPages())
    <ul class="neo-pagination mb-0">
        {{-- Tombol Previous --}}
        @if ($paginator->onFirstPage())
            <li class="neo-page-item disabled" aria-disabled="true">
                <span class="neo-page-link">&lsaquo;</span>
            </li>
        @else
            <li class="neo-page-item">
                <a class="neo-page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">&lsaquo;</a>
            </li>
        @endif

        {{-- Logika Penomoran Custom --}}
        @php
            $current = $paginator->currentPage();
            $last = $paginator->lastPage();

            // Tentukan batas awal dan akhir angka di tengah (1 di kiri, 1 di kanan)
            $start = $current - 1;
            $end = $current + 1;

            // Jika di halaman 1 atau 2, paksa agar menampilkan angka 1, 2, 3
            if ($start < 2) {
                $start = 1;
                $end = 3;
            }
            // Jika mendekati halaman terakhir, sesuaikan batasannya
            if ($end >= $last - 1) {
                $end = $last;
                $start = $last - 2;
            }

            // Pastikan tidak melewati batas minimal dan maksimal halaman
            $start = max(1, $start);
            $end = min($last, $end);
        @endphp

        {{-- Angka 1 (Halaman Pertama) --}}
        @if ($start > 1)
            <li class="neo-page-item"><a class="neo-page-link" href="{{ $paginator->url(1) }}">1</a></li>
            @if ($start > 2)
                <li class="neo-page-item disabled"><span class="neo-page-link">...</span></li>
            @endif
        @endif

        {{-- Looping Angka Tengah --}}
        @for ($i = $start; $i <= $end; $i++)
            @if ($i == $current)
                <li class="neo-page-item active" aria-current="page"><span class="neo-page-link">{{ $i }}</span></li>
            @else
                <li class="neo-page-item"><a class="neo-page-link" href="{{ $paginator->url($i) }}">{{ $i }}</a></li>
            @endif
        @endfor

        {{-- Angka Terakhir --}}
        @if ($end < $last)
            @if ($end < $last - 1)
                <li class="neo-page-item disabled"><span class="neo-page-link">...</span></li>
            @endif
            <li class="neo-page-item"><a class="neo-page-link" href="{{ $paginator->url($last) }}">{{ $last }}</a></li>
        @endif

        {{-- Tombol Next --}}
        @if ($paginator->hasMorePages())
            <li class="neo-page-item">
                <a class="neo-page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">&rsaquo;</a>
            </li>
        @else
            <li class="neo-page-item disabled" aria-disabled="true">
                <span class="neo-page-link">&rsaquo;</span>
            </li>
        @endif
    </ul>
@endif
