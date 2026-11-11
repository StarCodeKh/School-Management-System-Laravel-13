{{-- List / grid switch for pages that offer both layouts. <x-view-switch :list="route(...)" :grid="route(...)" :current="'grid'" /> --}}
@props(['list', 'grid', 'current' => 'list'])

<div {{ $attributes->class('inline-flex w-fit shrink-0 rounded-lg bg-slate-100 p-0.5 ring-1 ring-slate-200 ring-inset') }} role="group" aria-label="Layout">
    @foreach (['list' => ['List', 'list', $list], 'grid' => ['Grid', 'squares', $grid]] as $key => [$label, $icon, $href])
        <a href="{{ $href }}" @if ($current === $key) aria-current="page" @endif @class([
            'inline-flex items-center gap-1.5 rounded-md px-2.5 py-1.5 text-sm font-medium transition',
            'bg-white text-primary-700 shadow-sm ring-1 ring-slate-200' => $current === $key,
            'text-slate-500 hover:text-slate-800' => $current !== $key,
        ])>
            <x-icon :name="$icon" class="size-4" />
            <span class="hidden sm:inline">{{ $label }}</span>
        </a>
    @endforeach
</div>
