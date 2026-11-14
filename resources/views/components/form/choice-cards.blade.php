{{--
    A short list of options as selectable cards (radio buttons underneath), for choices that deserve a sentence each.
    :options as [value => ['label' => ..., 'description' => ..., 'color' => green|amber|red|indigo|slate]].
    Attributes such as x-model go on every radio.
--}}
@props(['name', 'label' => null, 'options' => [], 'value' => null, 'required' => false, 'hint' => null, 'wrapper' => '', 'cols' => 3])

@php
    $selected = (string) old($name, $value instanceof \BackedEnum ? $value->value : $value);
    $dots = ['green' => 'bg-emerald-500', 'amber' => 'bg-amber-500', 'red' => 'bg-red-500', 'indigo' => 'bg-primary-500', 'slate' => 'bg-slate-400'];
@endphp

<x-form.field :name="$name" :label="$label" :required="$required" :hint="$hint" :class="$wrapper">
    <div role="radiogroup" @class(['grid gap-2', 'sm:grid-cols-2' => (int) $cols >= 2, 'lg:grid-cols-3' => (int) $cols === 3])>
        @foreach ($options as $optionValue => $option)
            <label class="relative flex cursor-pointer gap-3 rounded-xl border border-slate-200 bg-white p-3 transition hover:border-slate-300 has-checked:border-primary-500 has-checked:bg-primary-50/50 has-focus-visible:outline-2 has-focus-visible:outline-offset-2 has-focus-visible:outline-primary-500">
                <input type="radio" name="{{ $name }}" value="{{ $optionValue }}" @checked($selected === (string) $optionValue) @required($required)
                       {{ $attributes->class('peer sr-only') }}>
                <span class="mt-1.5 size-2 shrink-0 rounded-full {{ $dots[$option['color'] ?? 'slate'] ?? $dots['slate'] }}"></span>
                <span class="min-w-0">
                    <span class="block text-sm font-semibold text-slate-900">{{ $option['label'] }}</span>
                    @if (! empty($option['description']))
                        <span class="mt-0.5 block text-xs text-slate-500">{{ $option['description'] }}</span>
                    @endif
                </span>
                <x-icon name="check-circle" class="absolute top-3 right-3 size-4 text-primary-600 opacity-0 transition peer-checked:opacity-100" />
            </label>
        @endforeach
    </div>
</x-form.field>
