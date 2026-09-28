{{-- rel="prev" / rel="next" hints for the <head>, pushed on the 'head' stack by paginated layouts --}}
@if ($items instanceof \Illuminate\Contracts\Pagination\Paginator)
    @unless ($items->onFirstPage())
        <link rel="prev" href="{{ $items->previousPageUrl() }}">
    @endunless
    @if ($items->hasMorePages())
        <link rel="next" href="{{ $items->nextPageUrl() }}">
    @endif
@endif
