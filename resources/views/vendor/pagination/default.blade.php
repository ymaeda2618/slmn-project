@if ($paginator->hasPages())
    <ul class="pagination" role="navigation">

        {{-- « 前へ --}}
        @if ($paginator->onFirstPage())
            <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
        @else
            <li class="page-item">
                <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">&laquo;</a>
            </li>
        @endif

        {{-- 最初のページ --}}
        <li class="page-item {{ $paginator->currentPage() == 1 ? 'active' : '' }}">
            <a class="page-link" href="{{ $paginator->url(1) }}">1</a>
        </li>

        {{-- 「...」前方 --}}
        @if ($paginator->currentPage() > 4)
            <li class="page-item disabled"><span class="page-link">…</span></li>
        @endif

        {{-- 中央3ページ表示 --}}
        @for ($i = 2; $i <= $paginator->lastPage() - 1; $i++)
            @if (
                $i == $paginator->currentPage() - 1 ||
                $i == $paginator->currentPage() ||
                $i == $paginator->currentPage() + 1
            )
                <li class="page-item {{ $paginator->currentPage() == $i ? 'active' : '' }}">
                    <a class="page-link" href="{{ $paginator->url($i) }}">{{ $i }}</a>
                </li>
            @endif
        @endfor

        {{-- 「...」後方 --}}
        @if ($paginator->currentPage() < $paginator->lastPage() - 3)
            <li class="page-item disabled"><span class="page-link">…</span></li>
        @endif

        {{-- 最後のページ --}}
        @if ($paginator->lastPage() > 1)
            <li class="page-item {{ $paginator->currentPage() == $paginator->lastPage() ? 'active' : '' }}">
                <a class="page-link" href="{{ $paginator->url($paginator->lastPage()) }}">{{ $paginator->lastPage() }}</a>
            </li>
        @endif

        {{-- » 次へ --}}
        @if ($paginator->hasMorePages())
            <li class="page-item">
                <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">&raquo;</a>
            </li>
        @else
            <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
        @endif
    </ul>
@endif
