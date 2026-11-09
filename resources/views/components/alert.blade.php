{{--
    Inline alert for messages that belong on the page rather than in a toast.
    <x-alert type="warning" title="Heads up">Body text</x-alert>
    type: success | error | warning | info. Add :dismissible="true" for a close button.
--}}
@props(['type' => 'info', 'title' => null, 'dismissible' => false])

@php
    $styles = [
        'success' => ['icon' => 'check-circle', 'box' => 'border-emerald-200 bg-emerald-50 text-emerald-800', 'icon_class' => 'text-emerald-500'],
        'error' => ['icon' => 'x-circle', 'box' => 'border-red-200 bg-red-50 text-red-800', 'icon_class' => 'text-red-500'],
        'warning' => ['icon' => 'warning', 'box' => 'border-amber-200 bg-amber-50 text-amber-800', 'icon_class' => 'text-amber-500'],
        'info' => ['icon' => 'information', 'box' => 'border-sky-200 bg-sky-50 text-sky-800', 'icon_class' => 'text-sky-500'],
    ];
    $style = $styles[$type] ?? $styles['info'];
@endphp

<div @if ($dismissible) x-data="{ show: true }" x-show="show" x-transition.opacity @endif
     role="{{ $type === 'error' ? 'alert' : 'status' }}"
     {{ $attributes->class(['flex items-start gap-3 rounded-lg border p-3 text-sm', $style['box']]) }}>
    <x-icon :name="$style['icon']" :class="'size-5 shrink-0 '.$style['icon_class']" />
    <div class="min-w-0 flex-1">
        @if ($title)
            <p class="font-semibold">{{ $title }}</p>
        @endif
        <div @class(['mt-0.5' => $title])>{{ $slot }}</div>
    </div>
    @if ($dismissible)
        <button type="button" class="-m-1 rounded-md p-1 opacity-60 hover:opacity-100" @click="show = false">
            <span class="sr-only">Dismiss</span>
            <x-icon name="x-mark" class="size-4" />
        </button>
    @endif
</div>
