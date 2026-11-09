{{--
    Small square icon action used in table rows, with a tooltip.
      tone: slate | primary | danger | success
      confirm (+ confirm-title, confirm-label, confirm-tone): ask in the confirmation dialog first
    Renders <a> with href, otherwise <button type="button"> (pass type="submit" inside a form).
--}}
@props(['href' => null, 'icon', 'label', 'tone' => 'slate', 'confirm' => null, 'confirmTitle' => null, 'confirmLabel' => null, 'confirmTone' => null])

@php
    $classes = \Illuminate\Support\Arr::toCssClasses([
        'inline-grid size-8 place-items-center rounded-lg transition duration-150 active:scale-90',
        'focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-primary-500',
        'text-slate-500 hover:bg-slate-100 hover:text-slate-800' => $tone === 'slate',
        'text-primary-600 hover:bg-primary-50 hover:text-primary-700' => $tone === 'primary',
        'text-red-600 hover:bg-red-50 hover:text-red-700' => $tone === 'danger',
        'text-emerald-600 hover:bg-emerald-50 hover:text-emerald-700' => $tone === 'success',
    ]);
    $extra = array_filter([
        'aria-label' => $label,
        'data-tooltip' => $label,
        'data-confirm' => $confirm,
        'data-confirm-title' => $confirm ? $confirmTitle : null,
        'data-confirm-label' => $confirm ? $confirmLabel : null,
        'data-confirm-tone' => $confirm ? $confirmTone : null,
    ], fn ($value) => $value !== null);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge($extra)->class($classes) }}>
        <x-icon :name="$icon" class="size-4" />
    </a>
@else
    <button {{ $attributes->merge(['type' => 'button'] + $extra)->class($classes) }}>
        <x-icon :name="$icon" class="size-4" />
    </button>
@endif
