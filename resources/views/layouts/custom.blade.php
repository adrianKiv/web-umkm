@if ($paginator->hasPages())
    <ul class="pagination mb-0">
        {{-- Tombol Previous --}}
        @if ($paginator->onFirstPage())
            <li class="page-item disabled" aria-disabled="true">
                <span class="page-link">&lsaquo;</span>
            </li>
        @else
            <li class="page-item">
                <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">&lsaquo;</a>
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
            <li class="page-item"><a class="page-link" href="{{ $paginator->url(1) }}">1</a></li>
            @if ($start > 2)
                <li class="page-item disabled"><span class="page-link">...</span></li>
            @endif
        @endif

        {{-- Looping Angka Tengah --}}
        @for ($i = $start; $i <= $end; $i++)
            @if ($i == $current)
                <li class="page-item active" aria-current="page"><span class="page-link">{{ $i }}</span></li>
            @else
                <li class="page-item"><a class="page-link" href="{{ $paginator->url($i) }}">{{ $i }}</a></li>
            @endif
        @endfor

        {{-- Angka Terakhir --}}
        @if ($end < $last)
            @if ($end < $last - 1)
                <li class="page-item disabled"><span class="page-link">...</span></li>
            @endif
            <li class="page-item"><a class="page-link" href="{{ $paginator->url($last) }}">{{ $last }}</a></li>
        @endif

        {{-- Tombol Next --}}
        @if ($paginator->hasMorePages())
            <li class="page-item">
                <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">&rsaquo;</a>
            </li>
        @else
            <li class="page-item disabled" aria-disabled="true">
                <span class="page-link">&rsaquo;</span>
            </li>
        @endif
    </ul>
@endif
