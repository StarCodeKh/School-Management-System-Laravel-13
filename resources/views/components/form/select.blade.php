{{--
    Searchable dropdown backed by a real <select> (see selectMenu in resources/js/app.js).
    Pass :options as [value => label] (optionally a nested [group => [value => label]]), or render options in the slot.
    icon: leading icon name. searchable: true/false (default: on when there are more than 8 options).
    size: md | sm (sm for filters and table rows). Attributes such as x-model and onchange go on the real <select>.
    group-filter: a JS expression naming the one optgroup to list, e.g. group-filter="classGroups[classId]" for
    dependent dropdowns; a selection outside that group is cleared when it changes.
--}}
@props([
    'name', 'label' => null, 'options' => null, 'value' => null, 'placeholder' => 'Please select', 'required' => false,
    'hint' => null, 'wrapper' => '', 'icon' => null, 'searchable' => null, 'size' => 'md', 'groupFilter' => null,
])

@php
    $key = str_replace(['[', ']'], ['.', ''], $name);
    $id = $attributes->get('id', $name);
    $selected = (string) old($key, $value instanceof \BackedEnum ? $value->value : $value);
    $hasError = $errors->has($key);

    // label shown before Alpine boots, so the page doesn't flash the placeholder
    $initialLabel = null;
    foreach (is_iterable($options) ? $options : [] as $optionValue => $optionLabel) {
        foreach (is_iterable($optionLabel) ? $optionLabel : [$optionValue => $optionLabel] as $v => $l) {
            if ((string) $v === $selected && $selected !== '') {
                $initialLabel = $l;
            }
        }
    }
    $small = $size === 'sm';

    // a matching icon for common fields; icon="name" picks another, :icon="false" turns it off
    $nameIcons = [
        'student_id' => 'user', 'user_id' => 'user', 'customer_id' => 'user', 'teacher_id' => 'academic-cap',
        'school_class_id' => 'academic-cap', 'class' => 'academic-cap', 'section_id' => 'squares', 'subject_id' => 'book',
        'book_id' => 'book', 'department_id' => 'building', 'vehicle_id' => 'truck', 'hostel_room_id' => 'home-modern',
        'fee_type_id' => 'banknotes', 'payment_method' => 'banknotes', 'category' => 'tag', 'role' => 'shield',
        'status' => 'check-circle', 'audience' => 'megaphone', 'day_of_week' => 'calendar', 'recurring_by_month' => 'calendar',
        'pickup_stop' => 'map',
    ];
    $icon = $icon === false ? null : ($icon ?? $nameIcons[$key] ?? null);
@endphp

<x-form.field :name="$name" :label="$label" :required="$required" :hint="$hint" :for="$id" :class="$wrapper">
    <div x-data="selectMenu({ searchable: @js($searchable){!! $groupFilter ? ', groupFilter: () => ('.e($groupFilter).')' : '' !!} })" class="relative" @keydown.escape.prevent.stop="open && close(true)">
        {{-- the real control: submitted with the form, invisible, sits under the button so browser validation points here --}}
        <select x-ref="native" name="{{ $name }}" id="{{ $id }}-native" tabindex="-1" aria-hidden="true" @required($required)
                {{ $attributes->except(['class', 'id', 'x-bind:disabled', ':disabled']) }}
                @if ($attributes->has('x-bind:disabled')) x-bind:disabled="{{ $attributes->get('x-bind:disabled') }}" @endif
                class="pointer-events-none absolute inset-0 size-full opacity-0">
            @if ($placeholder !== false)
                <option value="" @selected($selected === '')>{{ $placeholder }}</option>
            @endif
            @if (is_iterable($options))
                @foreach ($options as $optionValue => $optionLabel)
                    @if (is_iterable($optionLabel))
                        <optgroup label="{{ $optionValue }}">
                            @foreach ($optionLabel as $groupValue => $groupLabel)
                                <option value="{{ $groupValue }}" @selected($selected === (string) $groupValue)>{{ $groupLabel }}</option>
                            @endforeach
                        </optgroup>
                    @else
                        <option value="{{ $optionValue }}" @selected($selected === (string) $optionValue)>{{ $optionLabel }}</option>
                    @endif
                @endforeach
            @endif
            {{ $slot }}
        </select>

        <button type="button" id="{{ $id }}" x-ref="button" @click="toggle()" @keydown="onButtonKey($event)" :disabled="disabled"
                aria-haspopup="listbox" :aria-expanded="open.toString()"
                @class([
                    'relative flex w-full items-center gap-2 rounded-lg border bg-white text-left shadow-sm transition focus:ring-1 focus:outline-none disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-500',
                    'py-2 pr-2 pl-3 text-sm' => ! $small,
                    'py-1 pr-1.5 pl-2.5 text-xs' => $small,
                    'border-slate-300 focus:border-primary-500 focus:ring-primary-500' => ! $hasError,
                    'border-red-400 focus:border-red-500 focus:ring-red-500' => $hasError,
                ])
                :class="open && '{{ $hasError ? 'border-red-500 ring-1 ring-red-500' : 'border-primary-500 ring-1 ring-primary-500' }}'">
            @if ($icon)
                <x-icon :name="$icon" @class(['text-slate-400', 'size-4' => ! $small, 'size-3.5' => $small]) />
            @endif
            <span class="min-w-0 flex-1 truncate" :class="selected ? 'text-slate-900' : 'text-slate-400'" x-text="selected ? selected.label : placeholder"
                  @class(['text-slate-900' => $initialLabel !== null, 'text-slate-400' => $initialLabel === null])>{{ $initialLabel ?? ($placeholder === false ? '' : $placeholder) }}</span>
            <span x-show="clearable" x-cloak role="button" tabindex="-1" @click.stop="clear()" title="Clear"
                  class="rounded p-0.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                <x-icon name="x-mark" class="size-3.5" />
            </span>
            <x-icon name="chevron-up-down" @class(['text-slate-400 transition', 'size-4' => ! $small, 'size-3.5' => $small]) />
        </button>

        <template x-teleport="body">
            <div x-show="open" x-cloak :style="panelStyle" @keydown="onPanelKey($event)"
                 @click.outside="if (! $refs.button.contains($event.target)) close()"
                 x-transition:enter="transition duration-100 ease-out" x-transition:enter-start="scale-95 opacity-0" x-transition:enter-end="scale-100 opacity-100"
                 x-transition:leave="transition duration-75 ease-in" x-transition:leave-end="opacity-0"
                 class="z-[70] overflow-hidden rounded-lg bg-white text-sm shadow-lg ring-1 ring-slate-900/10">
                <div x-show="searchable" class="relative border-b border-slate-100">
                    <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400" />
                    <input x-ref="search" type="text" x-model="query" @input="active = 0" placeholder="Search..." autocomplete="off"
                           class="block w-full border-0 py-2.5 pr-3 pl-9 text-sm placeholder:text-slate-400 focus:ring-0">
                </div>
                <ul x-ref="list" tabindex="-1" role="listbox" class="max-h-64 overflow-y-auto p-1 focus:outline-none">
                    <template x-for="row in rows" :key="row.key">
                        <li>
                            <template x-if="row.heading">
                                <p class="px-2.5 pt-2 pb-1 text-[11px] font-semibold tracking-wide text-slate-400 uppercase" x-text="row.heading"></p>
                            </template>
                            <template x-if="row.option">
                                <div role="option" :aria-selected="(row.option.value === current).toString()" :data-active="(row.index === active).toString()"
                                     @click="pick(row.option)" @mousemove="active = row.index"
                                     class="flex cursor-pointer items-center gap-2 rounded-md px-2.5 py-2"
                                     :class="{
                                         'bg-primary-50 text-primary-700': row.index === active,
                                         'text-slate-700': row.index !== active,
                                         'font-semibold': row.option.value === current,
                                         'italic text-slate-400': row.option.placeholder,
                                         'cursor-not-allowed opacity-50': row.option.disabled,
                                     }">
                                    <span class="min-w-0 flex-1 truncate" x-text="row.option.label"></span>
                                    <x-icon name="check" class="size-4 text-primary-600" x-show="row.option.value === current" />
                                </div>
                            </template>
                        </li>
                    </template>
                    <li x-show="! rows.length" class="px-3 py-6 text-center text-sm text-slate-400">
                        <span x-text="query ? 'No matches for “' + query + '”' : 'No options available'"></span>
                    </li>
                </ul>
            </div>
        </template>
    </div>
</x-form.field>
