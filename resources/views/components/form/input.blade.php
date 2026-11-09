{{--
    Text-like input with label, error and hint. A leading icon is picked from the type
    (email, tel, password, date, month, time, url, search); pass icon="name" to choose one or :icon="false" for none.
    Password fields get a show/hide toggle.
--}}
@props(['name', 'label' => null, 'type' => 'text', 'value' => null, 'required' => false, 'hint' => null, 'wrapper' => '', 'icon' => null])

@php
    $key = str_replace(['[', ']'], ['.', ''], $name);
    $current = $type === 'password' || $type === 'file' ? null : old($key, $value instanceof \DateTimeInterface ? $value->format($type === 'month' ? 'Y-m' : 'Y-m-d') : $value);
    $typeIcons = ['email' => 'envelope', 'tel' => 'phone', 'password' => 'lock', 'date' => 'calendar', 'month' => 'calendar', 'time' => 'clock', 'url' => 'link', 'search' => 'search'];
    $icon = $icon === false || $type === 'file' ? null : ($icon ?? $typeIcons[$type] ?? null);
    $isPassword = $type === 'password';
    $hasError = $errors->has($key);
@endphp

<x-form.field :name="$name" :label="$label" :required="$required" :hint="$hint" :class="$wrapper">
    <div class="relative" @if ($isPassword) x-data="{ reveal: false }" @endif>
        @if ($icon)
            <x-icon :name="$icon" @class(['pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2', 'text-slate-400' => ! $hasError, 'text-red-400' => $hasError]) />
        @endif

        <input type="{{ $type }}" name="{{ $name }}" id="{{ $attributes->get('id', $name) }}"
               @if ($isPassword) x-bind:type="reveal ? 'text' : 'password'" @endif
               @if ($current !== null) value="{{ $current }}" @endif
               @required($required)
               {{ $attributes->class([
                   'block w-full rounded-lg text-sm shadow-sm placeholder:text-slate-400',
                   'pl-9' => $icon,
                   'pr-10' => $isPassword,
                   'border-slate-300 focus:border-primary-500 focus:ring-primary-500' => ! $hasError,
                   'border-red-400 text-red-900 focus:border-red-500 focus:ring-red-500' => $hasError,
                   'file:mr-3 file:rounded-md file:border-0 file:bg-primary-50 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-primary-700 hover:file:bg-primary-100 border p-1.5' => $type === 'file',
                   'bg-slate-50 text-slate-500' => $attributes->has('readonly'),
               ]) }}>

        @if ($isPassword)
            <button type="button" @click="reveal = ! reveal" class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-slate-600"
                    :title="reveal ? 'Hide password' : 'Show password'">
                <span class="sr-only" x-text="reveal ? 'Hide password' : 'Show password'">Show password</span>
                <x-icon name="eye" class="size-4" x-show="! reveal" />
                <x-icon name="eye-slash" class="size-4" x-show="reveal" x-cloak />
            </button>
        @endif
    </div>
</x-form.field>
