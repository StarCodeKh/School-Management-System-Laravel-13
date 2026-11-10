{{-- A dashed placeholder box for a page or grid with nothing to show yet. Use <x-empty-row> inside tables. --}}
@props(['icon' => null])

<div {{ $attributes->class('flex flex-col items-center gap-2 rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center text-sm text-slate-500') }}>
    @if ($icon)
        <span class="grid size-10 place-items-center rounded-full bg-slate-100 text-slate-400"><x-icon :name="$icon" class="size-5" /></span>
    @endif
    <div>{{ $slot }}</div>
</div>
