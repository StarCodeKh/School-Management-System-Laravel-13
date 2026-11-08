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

<div {{ $attributes->class('flex items-center gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm') }}>
    <span class="grid size-12 shrink-0 place-items-center rounded-xl {{ $tones[$color] ?? $tones['indigo'] }}">
        <x-icon :name="$icon" class="size-6" />
    </span>
    <div class="min-w-0">
        <p class="text-sm text-slate-500">{{ $label }}</p>
        <p class="truncate text-2xl font-bold text-slate-900">
            @if ($href)<a href="{{ $href }}" class="hover:text-primary-600">{{ $value }}</a>@else{{ $value }}@endif
        </p>
    </div>
</div>
