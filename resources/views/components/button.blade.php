{{--
    The app's button. Renders <a> when href is given, otherwise <button type="submit"> (pass type="button" for scripts).
      variant   primary | secondary | soft | ghost | danger | danger-soft | success | link
      size      xs | sm | md (38px, lines up with inputs) | lg
      icon / icon-right   leading / trailing icon; an icon with no text gives a square icon button (pass label for its tooltip)
      label     accessible name + tooltip for icon-only buttons
      loading   show the spinner and disable (forms do this automatically on submit)
      disabled  also works for links
      confirm   ask in the confirmation dialog first; confirm-title, confirm-label, confirm-tone (danger | primary)
--}}
@props([
    'variant' => 'primary', 'href' => null, 'size' => 'md', 'icon' => null, 'iconRight' => null, 'label' => null,
    'loading' => false, 'disabled' => false,
    'confirm' => null, 'confirmTitle' => null, 'confirmLabel' => null, 'confirmTone' => null,
])

@php
    $iconOnly = $icon && trim((string) $slot) === '';
    $iconSize = ['xs' => 'size-3.5', 'sm' => 'size-3.5', 'md' => 'size-4', 'lg' => 'size-5'][$size] ?? 'size-4';

    $classes = \Illuminate\Support\Arr::toCssClasses([
        'relative inline-flex shrink-0 items-center justify-center rounded-lg font-semibold whitespace-nowrap select-none transition duration-150',
        'focus-visible:outline-2 focus-visible:outline-offset-2 active:scale-[.97]',
        'disabled:pointer-events-none disabled:opacity-50 aria-disabled:pointer-events-none aria-disabled:opacity-50',
        // sizes
        'h-7 gap-1.5 text-xs' => $size === 'xs',
        'h-8 gap-1.5 text-xs' => $size === 'sm',
        'h-9.5 gap-2 text-sm' => $size === 'md',
        'h-11 gap-2 text-base' => $size === 'lg',
        'px-2.5' => ! $iconOnly && $size === 'xs',
        'px-3' => ! $iconOnly && $size === 'sm',
        'px-4' => ! $iconOnly && $size === 'md',
        'px-5' => ! $iconOnly && $size === 'lg',
        'aspect-square' => $iconOnly,
        // variants
        'bg-linear-to-b from-primary-500 to-primary-600 text-white shadow-sm shadow-primary-600/25 ring-1 ring-primary-700/20 ring-inset hover:from-primary-600 hover:to-primary-700 focus-visible:outline-primary-600' => $variant === 'primary',
        'bg-white text-slate-700 shadow-sm ring-1 ring-slate-300 ring-inset hover:bg-slate-50 hover:text-slate-900 focus-visible:outline-slate-400' => $variant === 'secondary',
        'bg-primary-50 text-primary-700 hover:bg-primary-100 focus-visible:outline-primary-500' => $variant === 'soft',
        'text-slate-600 hover:bg-slate-100 hover:text-slate-900 focus-visible:outline-slate-400' => $variant === 'ghost',
        'bg-linear-to-b from-red-500 to-red-600 text-white shadow-sm shadow-red-600/25 ring-1 ring-red-700/20 ring-inset hover:from-red-600 hover:to-red-700 focus-visible:outline-red-600' => $variant === 'danger',
        'bg-red-50 text-red-700 hover:bg-red-100 focus-visible:outline-red-500' => $variant === 'danger-soft',
        'bg-linear-to-b from-emerald-500 to-emerald-600 text-white shadow-sm shadow-emerald-600/25 ring-1 ring-emerald-700/20 ring-inset hover:from-emerald-600 hover:to-emerald-700 focus-visible:outline-emerald-600' => $variant === 'success',
        'h-auto! px-0! text-primary-600 hover:text-primary-700 hover:underline focus-visible:outline-primary-500 active:scale-100' => $variant === 'link',
    ]);

    $extra = array_filter([
        'aria-label' => $iconOnly ? $label : null,
        'data-tooltip' => $iconOnly ? $label : null,
        'data-confirm' => $confirm,
        'data-confirm-title' => $confirm ? $confirmTitle : null,
        'data-confirm-label' => $confirm ? $confirmLabel : null,
        'data-confirm-tone' => $confirm ? $confirmTone : null,
        'aria-busy' => $loading ? 'true' : null,
    ], fn ($value) => $value !== null);
@endphp

@if ($href)
    <a @unless ($disabled) href="{{ $href }}" @else aria-disabled="true" tabindex="-1" @endunless
       {{ $attributes->merge($extra)->class($classes) }}>
        @if ($icon)<x-icon :name="$icon" :class="$iconSize" />@endif
        {{ $slot }}
        @if ($iconRight)<x-icon :name="$iconRight" :class="$iconSize" />@endif
    </a>
@else
    <button @disabled($disabled || $loading) {{ $attributes->merge(['type' => 'submit'] + $extra)->class($classes) }}>
        @if ($icon)<x-icon :name="$icon" :class="$iconSize" />@endif
        {{ $slot }}
        @if ($iconRight)<x-icon :name="$iconRight" :class="$iconSize" />@endif
    </button>
@endif
