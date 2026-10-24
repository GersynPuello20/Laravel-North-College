@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Paginación" class="flex items-center justify-between gap-3 border-t border-slate-100 px-5 py-3 text-sm">
        <p class="text-slate-500">
            {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} de {{ $paginator->total() }}
        </p>
        <div class="flex items-center gap-1">
            @if ($paginator->onFirstPage())
                <span class="btn-secondary btn-sm opacity-50" aria-disabled="true"><x-icon name="chevron-left" class="size-4" /></span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="btn-secondary btn-sm" rel="prev" aria-label="Anterior"><x-icon name="chevron-left" class="size-4" /></a>
            @endif

            <span class="px-2 text-slate-600">Página {{ $paginator->currentPage() }} de {{ $paginator->lastPage() }}</span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="btn-secondary btn-sm" rel="next" aria-label="Siguiente"><x-icon name="chevron-right" class="size-4" /></a>
            @else
                <span class="btn-secondary btn-sm opacity-50" aria-disabled="true"><x-icon name="chevron-right" class="size-4" /></span>
            @endif
        </div>
    </nav>
@endif
