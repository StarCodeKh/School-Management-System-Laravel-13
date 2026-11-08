{{-- Page title with breadcrumbs. $breadcrumbs: ['Label' => url, 'Current' => null] --}}
@props(['title', 'breadcrumbs' => []])

<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        @if ($breadcrumbs)
            <nav class="mb-1 flex flex-wrap items-center gap-1 text-sm text-slate-500" aria-label="Breadcrumb">
                @foreach ($breadcrumbs as $label => $url)
                    @if (! $loop->first)
                        <x-icon name="chevron-right" class="size-3.5 text-slate-300" />
                    @endif
                    @if ($url)
                        <a href="{{ $url }}" class="hover:text-primary-600">{{ $label }}</a>
                    @else
                        <span class="text-slate-700">{{ $label }}</span>
                    @endif
                @endforeach
            </nav>
        @endif
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $title }}</h1>
    </div>

    @isset($actions)
        <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
    @endisset
</div>
