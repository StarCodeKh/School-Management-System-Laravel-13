{{--
    One entry of an <x-dropdown>. A link with href, otherwise a button (type="submit" inside a form).
    tone: slate | danger. Supports confirm / confirm-title / confirm-label / confirm-tone like <x-button>.
--}}
@props(['href' => null, 'icon' => null, 'tone' => 'slate', 'confirm' => null, 'confirmTitle' => null, 'confirmLabel' => null, 'confirmTone' => null])

@php
    $classes = \Illuminate\Support\Arr::toCssClasses([
        'flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-left text-sm transition focus:outline-none',
        'text-slate-700 hover:bg-slate-100 focus:bg-slate-100' => $tone !== 'danger',
        'text-red-600 hover:bg-red-50 focus:bg-red-50' => $tone === 'danger',
    ]);
    $extra = array_filter([
        'role' => 'menuitem',
        'data-confirm' => $confirm,
        'data-confirm-title' => $confirm ? $confirmTitle : null,
        'data-confirm-label' => $confirm ? $confirmLabel : null,
        'data-confirm-tone' => $confirm ? $confirmTone : null,
    ], fn ($value) => $value !== null);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge($extra)->class($classes) }}>
        @if ($icon)<x-icon :name="$icon" @class(['size-4', 'text-slate-400' => $tone !== 'danger']) />@endif
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['type' => 'button'] + $extra)->class($classes) }}>
        @if ($icon)<x-icon :name="$icon" @class(['size-4', 'text-slate-400' => $tone !== 'danger']) />@endif
        {{ $slot }}
    </button>
@endif
