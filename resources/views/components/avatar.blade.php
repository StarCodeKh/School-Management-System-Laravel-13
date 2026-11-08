@props(['src', 'alt' => '', 'size' => 'size-9'])

<img src="{{ $src }}" alt="{{ $alt }}" {{ $attributes->class([$size, 'shrink-0 rounded-full object-cover ring-1 ring-slate-200']) }}>
