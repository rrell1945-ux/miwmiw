@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}">
        <div class="flex gap-2 items-center justify-between">
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-400 bg-white/70 dark:bg-gray-800 border border-pink-100 dark:border-gray-700 cursor-not-allowed rounded-xl">
                    {!! __('pagination.previous') !!}
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center px-4 py-2 text-sm font-medium text-pink-600 dark:text-pink-300 bg-white/70 dark:bg-gray-800 border border-pink-200 dark:border-gray-600 rounded-xl shadow-card hover:bg-pink-50 dark:hover:bg-gray-700 hover:-translate-y-0.5 transition-all duration-200">
                    {!! __('pagination.previous') !!}
                </a>
            @endif

            <p class="text-sm text-gray-500 dark:text-gray-300">
                {!! __('Showing') !!}
                @if ($paginator->firstItem())
                    <span class="font-semibold">{{ $paginator->firstItem() }}</span>
                    {!! __('to') !!}
                    <span class="font-semibold">{{ $paginator->lastItem() }}</span>
                @else
                    {{ $paginator->count() }}
                @endif
                {!! __('of') !!}
                <span class="font-semibold">{{ $paginator->total() }}</span>
            </p>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center px-4 py-2 text-sm font-medium text-pink-600 dark:text-pink-300 bg-white/70 dark:bg-gray-800 border border-pink-200 dark:border-gray-600 rounded-xl shadow-card hover:bg-pink-50 dark:hover:bg-gray-700 hover:-translate-y-0.5 transition-all duration-200">
                    {!! __('pagination.next') !!}
                </a>
            @else
                <span class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-400 bg-white/70 dark:bg-gray-800 border border-pink-100 dark:border-gray-700 cursor-not-allowed rounded-xl">
                    {!! __('pagination.next') !!}
                </span>
            @endif
        </div>
    </nav>
@endif
