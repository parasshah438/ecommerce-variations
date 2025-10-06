@if ($paginator->hasPages())
    <nav class="d-flex justify-content-center">
        <ul class="pagination pagination-sm" style="font-size: 12px;">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled">
                    <span class="page-link" style="font-size: 10px !important; padding: 4px 6px !important; width: 26px !important; height: 26px !important; display: flex !important; align-items: center !important; justify-content: center !important; line-height: 1 !important;">‹</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" style="font-size: 10px !important; padding: 4px 6px !important; width: 26px !important; height: 26px !important; display: flex !important; align-items: center !important; justify-content: center !important; line-height: 1 !important;">‹</a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li class="page-item disabled">
                        <span class="page-link" style="font-size: 11px !important; padding: 4px 6px !important; height: 26px !important; display: flex !important; align-items: center !important; justify-content: center !important; line-height: 1 !important;">{{ $element }}</span>
                    </li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active">
                                <span class="page-link" style="font-size: 11px !important; padding: 4px 6px !important; height: 26px !important; display: flex !important; align-items: center !important; justify-content: center !important; line-height: 1 !important;">{{ $page }}</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $url }}" style="font-size: 11px !important; padding: 4px 6px !important; height: 26px !important; display: flex !important; align-items: center !important; justify-content: center !important; line-height: 1 !important;">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" style="font-size: 10px !important; padding: 4px 6px !important; width: 26px !important; height: 26px !important; display: flex !important; align-items: center !important; justify-content: center !important; line-height: 1 !important;">›</a>
                </li>
            @else
                <li class="page-item disabled">
                    <span class="page-link" style="font-size: 10px !important; padding: 4px 6px !important; width: 26px !important; height: 26px !important; display: flex !important; align-items: center !important; justify-content: center !important; line-height: 1 !important;">›</span>
                </li>
            @endif
        </ul>
    </nav>
@endif