{{--
    Image field with a live preview, drag and drop, file name and size, and undo (imageUpload in resources/js/app.js).
      current   URL of the image saved now (shown until a new one is picked)
      shape     circle (photos) | square | wide (signatures, logos)
      max-kb    size limit checked in the browser; keep it in line with the server rule (default 2048 = 2 MB)
--}}
@props(['name', 'label' => null, 'current' => null, 'shape' => 'circle', 'required' => false, 'hint' => null, 'maxKb' => 2048, 'wrapper' => ''])

@php
    $key = str_replace(['[', ']'], ['.', ''], $name);
    $id = $attributes->get('id', $name);
    $hasError = $errors->has($key);
    $frame = [
        'circle' => 'size-20 rounded-full',
        'square' => 'size-20 rounded-xl',
        'wide' => 'h-20 w-40 rounded-xl',
    ][$shape] ?? 'size-20 rounded-full';
@endphp

<x-form.field :name="$name" :label="$label" :required="$required" :hint="$hint ?? 'JPG, PNG or GIF, up to '.round($maxKb / 1024, 1).' MB.'" :for="$id" :class="$wrapper">
    <div x-data="imageUpload({ name: @js($name), current: @js($current), maxKb: {{ (int) $maxKb }} })"
         @dragover.prevent="dragging = true" @dragleave.prevent="dragging = false" @drop.prevent="onDrop($event)"
         @class([
             'flex flex-wrap items-center gap-4 rounded-xl border-2 border-dashed p-4 transition',
             'border-slate-200 bg-slate-50/60' => ! $hasError,
             'border-red-300 bg-red-50/40' => $hasError,
         ])
         :class="dragging && 'border-primary-400! bg-primary-50!'">
        <div class="relative shrink-0">
            <div class="{{ $frame }} grid place-items-center overflow-hidden bg-white ring-1 ring-slate-200">
                <img x-show="shown" :src="shown" alt="" class="size-full object-cover" @if ($current) src="{{ $current }}" @else x-cloak @endif>
                <x-icon name="upload" class="size-6 text-slate-300" x-show="! shown" />
            </div>
            <span x-show="preview" x-cloak class="absolute -top-1 -right-1 rounded-full bg-primary-600 px-1.5 py-0.5 text-[10px] font-semibold text-white shadow-sm">New</span>
        </div>

        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
                <x-button type="button" variant="secondary" size="sm" icon="upload" @click="choose()">
                    <span x-text="shown ? 'Change' : 'Upload'">{{ $current ? 'Change' : 'Upload' }}</span>
                </x-button>
                <x-button type="button" variant="ghost" size="sm" x-show="preview" x-cloak @click="reset()">Undo</x-button>
            </div>
            <p class="mt-1.5 truncate text-sm text-slate-500">
                <span x-show="fileName" x-cloak><span class="font-medium text-slate-700" x-text="fileName"></span> · <span x-text="fileSize"></span></span>
                <span x-show="! fileName">or drag an image here</span>
            </p>
        </div>

        <input type="file" x-ref="input" name="{{ $name }}" id="{{ $id }}" accept="image/*" @required($required) @change="onChange()"
               {{ $attributes->except(['id', 'accept'])->class('sr-only') }}>
    </div>
</x-form.field>
