@props(['label', 'value', 'icon', 'color' => 'indigo', 'href' => null])

@php
    $tones = [
        'indigo' => 'bg-primary-50 text-primary-600',
        'green' => 'bg-emerald-50 text-emerald-600',
        'amber' => 'bg-amber-50 text-amber-600',
        'sky' => 'bg-sky-50 text-sky-600',
        'red' => 'bg-red-50 text-red-600',
    ];
@endphp

{{-- narrow cards (two per row on phones) stack the icon above the text; wider ones sit side by side --}}
<div {{ $attributes->class('@container min-w-0 rounded-xl border border-slate-200 bg-white shadow-sm') }}>
    <div class="flex flex-col gap-3 p-4 @[14rem]:flex-row @[14rem]:items-center @[14rem]:gap-4 @[14rem]:p-5">
        <span class="grid size-10 shrink-0 place-items-center rounded-xl @[14rem]:size-12 {{ $tones[$color] ?? $tones['indigo'] }}">
            <x-icon :name="$icon" class="size-5 @[14rem]:size-6" />
        </span>
        <div class="min-w-0">
            <p class="truncate text-sm text-slate-500">{{ $label }}</p>
            {{-- the number shrinks on narrow cards; if it still doesn't fit, the tooltip shows it in full --}}
            <p class="truncate text-lg font-bold text-slate-900 tabular-nums @[14rem]:text-xl @[16rem]:text-2xl" data-tooltip="{{ $value }}">
                @if ($href)<a href="{{ $href }}" class="hover:text-primary-600">{{ $value }}</a>@else{{ $value }}@endif
            </p>
        </div>
    </div>
</div>
