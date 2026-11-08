{{-- Pass :options as [value => label] (optionally a nested [group => [value => label]]), or render options in the slot. --}}
@props(['name', 'label' => null, 'options' => null, 'value' => null, 'placeholder' => 'Please select', 'required' => false, 'hint' => null, 'wrapper' => ''])

@php
    $key = str_replace(['[', ']'], ['.', ''], $name);
    $selected = (string) old($key, $value instanceof \BackedEnum ? $value->value : $value);
@endphp

<x-form.field :name="$name" :label="$label" :required="$required" :hint="$hint" :class="$wrapper">
    <select name="{{ $name }}" id="{{ $attributes->get('id', $name) }}" @required($required)
            {{ $attributes->class([
                'block w-full rounded-lg text-sm shadow-sm',
                'border-slate-300 focus:border-primary-500 focus:ring-primary-500' => ! $errors->has($key),
                'border-red-400 focus:border-red-500 focus:ring-red-500' => $errors->has($key),
            ]) }}>
        @if ($placeholder !== false)
            <option value="" @selected($selected === '')>{{ $placeholder }}</option>
        @endif
        @if (is_iterable($options))
            @foreach ($options as $optionValue => $optionLabel)
                @if (is_iterable($optionLabel))
                    <optgroup label="{{ $optionValue }}">
                        @foreach ($optionLabel as $groupValue => $groupLabel)
                            <option value="{{ $groupValue }}" @selected($selected === (string) $groupValue)>{{ $groupLabel }}</option>
                        @endforeach
                    </optgroup>
                @else
                    <option value="{{ $optionValue }}" @selected($selected === (string) $optionValue)>{{ $optionLabel }}</option>
                @endif
            @endforeach
        @endif
        {{ $slot }}
    </select>
</x-form.field>
