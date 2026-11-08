@props(['name', 'label' => null, 'type' => 'text', 'value' => null, 'required' => false, 'hint' => null, 'wrapper' => ''])

@php
    $key = str_replace(['[', ']'], ['.', ''], $name);
    $current = $type === 'password' || $type === 'file' ? null : old($key, $value instanceof \DateTimeInterface ? $value->format('Y-m-d') : $value);
@endphp

<x-form.field :name="$name" :label="$label" :required="$required" :hint="$hint" :class="$wrapper">
    <input type="{{ $type }}" name="{{ $name }}" id="{{ $attributes->get('id', $name) }}"
           @if ($current !== null) value="{{ $current }}" @endif
           @required($required)
           {{ $attributes->class([
               'block w-full rounded-lg text-sm shadow-sm',
               'border-slate-300 focus:border-primary-500 focus:ring-primary-500' => ! $errors->has($key),
               'border-red-400 text-red-900 focus:border-red-500 focus:ring-red-500' => $errors->has($key),
               'file:mr-3 file:rounded-md file:border-0 file:bg-primary-50 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-primary-700 border p-1.5' => $type === 'file',
               'bg-slate-50 text-slate-500' => $attributes->has('readonly'),
           ]) }}>
</x-form.field>
