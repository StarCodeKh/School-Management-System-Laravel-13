{{-- Small square icon action used in table rows. --}}
@props(['href' => null, 'icon', 'label', 'tone' => 'slate'])

@php
    $classes = \Illuminate\Support\Arr::toCssClasses([
        'inline-grid size-8 place-items-center rounded-lg transition',
        'text-slate-500 hover:bg-slate-100 hover:text-slate-700' => $tone === 'slate',
        'text-primary-600 hover:bg-primary-50' => $tone === 'primary',
        'text-red-600 hover:bg-red-50' => $tone === 'danger',
    ]);
@endphp

@if ($href)
    <a href="{{ $href }}" title="{{ $label }}" {{ $attributes->class($classes) }}>
        <x-icon :name="$icon" class="size-4" /><span class="sr-only">{{ $label }}</span>
    </a>
@else
    <button title="{{ $label }}" {{ $attributes->merge(['type' => 'button'])->class($classes) }}>
        <x-icon :name="$icon" class="size-4" /><span class="sr-only">{{ $label }}</span>
    </button>
@endif
