@props(['href', 'icon' => 'user', 'note' => null])

<li>
    <a href="{{ $href }}" {{ $attributes->class('flex items-center gap-2 text-primary-600 hover:underline') }}>
        <x-icon :name="$icon" class="size-4 shrink-0" />
        <span class="truncate">{{ $slot }}@if ($note) <span class="text-slate-400">{{ $note }}</span>@endif</span>
    </a>
</li>
