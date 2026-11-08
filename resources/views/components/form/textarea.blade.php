@props(['name', 'label' => null, 'value' => null, 'rows' => 3, 'required' => false, 'hint' => null, 'wrapper' => ''])

@php($key = str_replace(['[', ']'], ['.', ''], $name))

<x-form.field :name="$name" :label="$label" :required="$required" :hint="$hint" :class="$wrapper">
    <textarea name="{{ $name }}" id="{{ $attributes->get('id', $name) }}" rows="{{ $rows }}" @required($required)
              {{ $attributes->class([
                  'block w-full rounded-lg text-sm shadow-sm',
                  'border-slate-300 focus:border-primary-500 focus:ring-primary-500' => ! $errors->has($key),
                  'border-red-400 focus:border-red-500 focus:ring-red-500' => $errors->has($key),
              ]) }}>{{ old($key, $value) }}</textarea>
</x-form.field>
