@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}">

        <div class="flex gap-2 items-center justify-between sm:hidden">

            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center px-4 py-2 text-sm font-medium text-neutral-500 bg-white border border-neutral-300 cursor-not-allowed leading-5 rounded-control dark:text-neutral-400 dark:bg-neutral-900 dark:border-neutral-700">
                    {!! __('pagination.previous') !!}
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center px-4 py-2 text-sm font-medium text-neutral-700 bg-white border border-neutral-300 leading-5 rounded-control hover:text-neutral-900 focus:outline-none focus:ring ring-neutral-300 focus:border-primary-500 active:bg-neutral-100 active:text-neutral-900 transition ease-in-out duration-150 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-300 dark:focus:border-primary-500 dark:active:bg-neutral-800 dark:hover:text-white hover:bg-neutral-50 dark:hover:bg-neutral-800">
                    {!! __('pagination.previous') !!}
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center px-4 py-2 text-sm font-medium text-neutral-700 bg-white border border-neutral-300 leading-5 rounded-control hover:text-neutral-900 focus:outline-none focus:ring ring-neutral-300 focus:border-primary-500 active:bg-neutral-100 active:text-neutral-900 transition ease-in-out duration-150 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-300 dark:focus:border-primary-500 dark:active:bg-neutral-800 dark:hover:text-white hover:bg-neutral-50 dark:hover:bg-neutral-800">
                    {!! __('pagination.next') !!}
                </a>
            @else
                <span class="inline-flex items-center px-4 py-2 text-sm font-medium text-neutral-500 bg-white border border-neutral-300 cursor-not-allowed leading-5 rounded-control dark:text-neutral-400 dark:bg-neutral-900 dark:border-neutral-700">
                    {!! __('pagination.next') !!}
                </span>
            @endif

        </div>

        <div class="hidden sm:flex-1 sm:flex sm:gap-2 sm:items-center sm:justify-between">

            <div>
                <p class="text-sm text-neutral-500 leading-5 dark:text-neutral-400">
                    {!! __('Showing') !!}
                    @if ($paginator->firstItem())
                        <span class="font-medium text-neutral-700 dark:text-neutral-200">{{ $paginator->firstItem() }}</span>
                        {!! __('to') !!}
                        <span class="font-medium text-neutral-700 dark:text-neutral-200">{{ $paginator->lastItem() }}</span>
                    @else
                        {{ $paginator->count() }}
                    @endif
                    {!! __('of') !!}
                    <span class="font-medium text-neutral-700 dark:text-neutral-200">{{ $paginator->total() }}</span>
                    {!! __('results') !!}
                </p>
            </div>

            <div>
                <span class="inline-flex rtl:flex-row-reverse shadow-card rounded-control overflow-hidden border border-neutral-300 dark:border-neutral-700">

                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                            <span class="inline-flex items-center px-2 py-2 text-sm font-medium text-neutral-400 bg-white border-e border-neutral-300 cursor-not-allowed leading-5 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-500" aria-hidden="true">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center px-2 py-2 text-sm font-medium text-neutral-500 bg-white border-e border-neutral-300 leading-5 hover:text-neutral-700 focus:outline-none focus:ring ring-neutral-300 focus:border-primary-500 active:bg-neutral-100 active:text-neutral-500 transition ease-in-out duration-150 dark:bg-neutral-900 dark:border-neutral-700 dark:active:bg-neutral-800 dark:focus:border-primary-500 dark:text-neutral-400 dark:hover:bg-neutral-800 dark:hover:text-white" aria-label="{{ __('pagination.previous') }}">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <span aria-disabled="true">
                                <span class="inline-flex items-center px-4 py-2 text-sm font-medium text-neutral-500 bg-white border-e border-neutral-300 cursor-default leading-5 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400">{{ $element }}</span>
                            </span>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page">
                                        <span class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-primary-600 border-e border-primary-700 cursor-default leading-5 dark:bg-primary-600">{{ $page }}</span>
                                    </span>
                                @else
                                    <a href="{{ $url }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-neutral-600 bg-white border-e border-neutral-300 leading-5 hover:text-neutral-900 focus:outline-none focus:ring ring-neutral-300 focus:border-primary-500 active:bg-neutral-100 active:text-neutral-600 transition ease-in-out duration-150 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-300 dark:hover:text-white dark:active:bg-neutral-800 dark:focus:border-primary-500 hover:bg-neutral-50 dark:hover:bg-neutral-800" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center px-2 py-2 text-sm font-medium text-neutral-500 bg-white leading-5 hover:text-neutral-700 focus:outline-none focus:ring ring-neutral-300 focus:border-primary-500 active:bg-neutral-100 active:text-neutral-500 transition ease-in-out duration-150 dark:bg-neutral-900 dark:active:bg-neutral-800 dark:focus:border-primary-500 dark:text-neutral-400 dark:hover:bg-neutral-800 dark:hover:text-white" aria-label="{{ __('pagination.next') }}">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    @else
                        <span aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                            <span class="inline-flex items-center px-2 py-2 text-sm font-medium text-neutral-400 bg-white cursor-not-allowed leading-5 dark:bg-neutral-900 dark:text-neutral-500" aria-hidden="true">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </span>
                    @endif
                </span>
            </div>
        </div>
    </nav>
@endif
