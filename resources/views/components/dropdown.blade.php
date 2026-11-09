{{--
    Menu that floats above everything (never clipped by tables). Slots: trigger (a button), default (menu items).
    <x-dropdown align="right" width="176">
        <x-slot:trigger><x-icon-link icon="chevron-down" label="More" /></x-slot:trigger>
        <x-dropdown.item icon="check" ...>Mark as paid</x-dropdown.item>
    </x-dropdown>
--}}
@props(['align' => 'right', 'width' => 192])

<div x-data="dropdown({ align: @js($align), width: {{ (int) $width }} })" {{ $attributes->class('relative inline-block text-left') }}>
    <div x-ref="trigger" @click="toggle()" @keydown.arrow-down.prevent="show()" :aria-expanded="open.toString()" aria-haspopup="menu">
        {{ $trigger }}
    </div>
    <template x-teleport="body">
        <div x-show="open" x-cloak x-ref="panel" role="menu" :style="panelStyle" @keydown="onKey($event)"
             @click.outside="if (! $refs.trigger.contains($event.target)) close()"
             x-transition:enter="transition duration-100 ease-out" x-transition:enter-start="scale-95 opacity-0" x-transition:enter-end="scale-100 opacity-100"
             x-transition:leave="transition duration-75 ease-in" x-transition:leave-end="opacity-0"
             class="z-[70] overflow-hidden rounded-xl bg-white p-1 text-sm shadow-lg ring-1 ring-slate-900/10">
            {{ $slot }}
        </div>
    </template>
</div>
