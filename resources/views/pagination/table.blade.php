{{--
    Default pagination for every ->links() call (set in AppServiceProvider):
    "Showing 1–15 of 134", a rows-per-page picker and compact page links.
--}}
@php
    $options = collect(\App\Support\TableQuery::PER_PAGE_OPTIONS)->push($paginator->perPage())->unique()->sort()->values();
    $showPicker = $paginator->total() > $options->first();

    // keep the current search, filters and sort when the page size changes
    $hidden = collect(\Illuminate\Support\Arr::dot(request()->except(['per_page', 'page'])))
        ->mapWithKeys(function ($value, $key) {
            $parts = explode('.', $key);
            $name = array_shift($parts).collect($parts)->map(fn ($part) => '['.$part.']')->implode('');

            return [$name => $value];
        })
        ->filter(fn ($value) => is_scalar($value) && $value !== '');

    $linkClass = 'grid h-8 min-w-8 place-items-center rounded-lg px-2 text-sm font-medium transition';
@endphp

@if ($paginator->total() > 0)
    <nav class="flex flex-col gap-3 text-sm sm:flex-row sm:items-center sm:justify-between" aria-label="Pagination">
        <p class="text-slate-500">
            Showing <span class="font-semibold text-slate-700">{{ number_format($paginator->firstItem()) }}</span>–<span class="font-semibold text-slate-700">{{ number_format($paginator->lastItem()) }}</span>
            of <span class="font-semibold text-slate-700">{{ number_format($paginator->total()) }}</span> {{ \Illuminate\Support\Str::plural('result', $paginator->total()) }}
        </p>

        <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
            @if ($showPicker)
                <form method="GET" data-live class="flex items-center gap-2">
                    @foreach ($hidden as $name => $value)
                        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                    @endforeach
                    <span class="text-slate-500">Rows</span>
                    <x-form.select name="per_page" :id="'per_page_'.$paginator->getPageName()" :options="$options->combine($options)" :value="$paginator->perPage()"
                                   :placeholder="false" :icon="false" :searchable="false" size="sm" wrapper="w-[4.5rem]" />
                </form>
            @endif

            @if ($paginator->hasPages())
                <div class="flex items-center gap-1">
                    @if ($paginator->onFirstPage())
                        <span class="{{ $linkClass }} cursor-not-allowed text-slate-300"><x-icon name="chevron-right" class="size-4 rotate-180" /><span class="sr-only">Previous</span></span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" data-live-link rel="prev" class="{{ $linkClass }} text-slate-500 hover:bg-slate-100 hover:text-slate-700">
                            <x-icon name="chevron-right" class="size-4 rotate-180" /><span class="sr-only">Previous</span>
                        </a>
                    @endif

                    @foreach ($elements as $element)
                        @if (is_string($element))
                            <span class="{{ $linkClass }} text-slate-400">…</span>
                        @else
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page" class="{{ $linkClass }} bg-primary-600 text-white shadow-sm">{{ $page }}</span>
                                @else
                                    <a href="{{ $url }}" data-live-link class="{{ $linkClass }} hidden text-slate-600 hover:bg-slate-100 hover:text-slate-900 sm:grid">{{ $page }}</a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" data-live-link rel="next" class="{{ $linkClass }} text-slate-500 hover:bg-slate-100 hover:text-slate-700">
                            <x-icon name="chevron-right" class="size-4" /><span class="sr-only">Next</span>
                        </a>
                    @else
                        <span class="{{ $linkClass }} cursor-not-allowed text-slate-300"><x-icon name="chevron-right" class="size-4" /><span class="sr-only">Next</span></span>
                    @endif
                </div>
            @endif
        </div>
    </nav>
@endif
