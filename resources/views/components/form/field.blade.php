{{-- Label + control + error/hint wrapper used by the form components. --}}
@props(['name' => null, 'label' => null, 'required' => false, 'hint' => null, 'for' => null])

@php($key = $name ? str_replace(['[', ']'], ['.', ''], $name) : null)

<div {{ $attributes }}>
    @if ($label)
        <label for="{{ $for ?? $name }}" class="mb-1.5 block text-sm font-medium text-slate-700">
            {{ $label }}@if ($required)<span class="text-red-500"> *</span>@endif
        </label>
    @endif
    {{ $slot }}
    @if ($key && $errors->has($key))
        <p class="mt-1.5 text-sm text-red-600" data-field-error>{{ $errors->first($key) }}</p>
    @elseif ($hint)
        <p class="mt-1.5 text-xs text-slate-500">{{ $hint }}</p>
    @endif
</div>
