{{-- Textarea with label, error and hint. With maxlength="..." it also shows a live character counter. --}}
@props(['name', 'label' => null, 'value' => null, 'rows' => 3, 'required' => false, 'hint' => null, 'wrapper' => ''])

@php
    $key = str_replace(['[', ']'], ['.', ''], $name);
    $max = $attributes->get('maxlength');
@endphp

<x-form.field :name="$name" :label="$label" :required="$required" :hint="$hint" :class="$wrapper">
    <div class="relative" @if ($max) x-data="{ count: 0 }" x-init="count = $refs.text.value.length" @endif>
        <textarea name="{{ $name }}" id="{{ $attributes->get('id', $name) }}" rows="{{ $rows }}" @required($required)
                  @if ($max) x-ref="text" @input="count = $event.target.value.length" @endif
                  {{ $attributes->class([
                      'block w-full rounded-lg text-sm shadow-sm placeholder:text-slate-400',
                      'pb-6' => $max,
                      'border-slate-300 focus:border-primary-500 focus:ring-4 focus:ring-primary-100' => ! $errors->has($key),
                      'border-red-400 focus:border-red-500 focus:ring-4 focus:ring-red-100' => $errors->has($key),
                  ]) }}>{{ old($key, $value) }}</textarea>
        @if ($max)
            <span class="pointer-events-none absolute right-3 bottom-1.5 text-xs text-slate-400" :class="count >= {{ (int) $max }} && 'text-red-500'">
                <span x-text="count">0</span>/{{ $max }}
            </span>
        @endif
    </div>
</x-form.field>
