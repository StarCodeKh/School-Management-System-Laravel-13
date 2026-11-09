{{--
    Checkbox, or an on/off switch with :switch="true". The label goes in the slot; hint (optional) sits under it.
    Checked state comes from old() first, then :checked. Pass value="..." for array fields like customers[];
    extra attributes (x-model, @change, ...) go on the real <input>.
--}}
@props(['name', 'value' => '1', 'checked' => false, 'switch' => false, 'hint' => null])

@php
    $key = str_replace(['[]', '[', ']'], ['', '.', ''], $name);
    $old = old($key);
    $isChecked = $old !== null
        ? (is_array($old) ? in_array((string) $value, array_map('strval', $old), true) : (bool) $old)
        : (bool) $checked;
    $id = $attributes->get('id', str_ends_with($name, '[]') ? $key.'_'.\Illuminate\Support\Str::slug((string) $value, '_') : $name);
@endphp

<label for="{{ $id }}" class="group inline-flex cursor-pointer items-start gap-2.5 text-sm text-slate-700 select-none has-disabled:cursor-not-allowed has-disabled:opacity-60">
    @if ($switch)
        <span class="relative mt-0.5 inline-flex shrink-0">
            <input type="checkbox" name="{{ $name }}" id="{{ $id }}" value="{{ $value }}" @checked($isChecked)
                   {{ $attributes->except('id')->class('peer sr-only') }}>
            <span class="h-5 w-9 rounded-full bg-slate-300 transition peer-checked:bg-primary-600 peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-primary-500"></span>
            <span class="absolute top-0.5 left-0.5 size-4 rounded-full bg-white shadow-sm ring-1 ring-slate-900/5 transition peer-checked:translate-x-4"></span>
        </span>
    @else
        <input type="checkbox" name="{{ $name }}" id="{{ $id }}" value="{{ $value }}" @checked($isChecked)
               {{ $attributes->except('id')->class('mt-0.5 size-4 shrink-0 cursor-pointer rounded border-slate-300 text-primary-600 transition focus:ring-primary-500') }}>
    @endif
    <span>
        <span class="font-medium">{{ $slot }}</span>
        @if ($hint)
            <span class="block text-xs font-normal text-slate-500">{{ $hint }}</span>
        @endif
    </span>
</label>
