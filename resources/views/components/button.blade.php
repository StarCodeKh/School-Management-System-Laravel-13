{{-- Renders <a> when href is given, otherwise <button>. --}}
@props(['variant' => 'primary', 'href' => null, 'size' => 'md', 'icon' => null])

@php
    $classes = \Illuminate\Support\Arr::toCssClasses([
        'inline-flex items-center justify-center gap-2 rounded-lg font-semibold shadow-sm transition focus-visible:outline-2 focus-visible:outline-offset-2 disabled:opacity-50',
        'px-3 py-1.5 text-xs' => $size === 'sm',
        'px-4 py-2 text-sm' => $size === 'md',
        'bg-primary-600 text-white hover:bg-primary-700 focus-visible:outline-primary-600' => $variant === 'primary',
        'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 focus-visible:outline-slate-400' => $variant === 'secondary',
        'bg-red-600 text-white hover:bg-red-700 focus-visible:outline-red-600' => $variant === 'danger',
        'text-slate-600 shadow-none hover:bg-slate-100' => $variant === 'ghost',
    ]);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>
        @if ($icon)<x-icon :name="$icon" class="size-4" />@endif
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['type' => 'submit'])->class($classes) }}>
        @if ($icon)<x-icon :name="$icon" class="size-4" />@endif
        {{ $slot }}
    </button>
@endif
