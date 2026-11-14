{{--
    Text-like input with label, error and hint. A leading icon is picked from the type
    (email, tel, password, date, month, time, url, search); pass icon="name" to choose one or :icon="false" for none.
    Password fields get a show/hide toggle.
    date and month fields open a calendar (datePicker in resources/js/app.js) over a real date/month input:
      after="start_date"  the earliest pickable day follows another field in the same form (date ranges)
      :native="true"      keep the browser's own picker instead
--}}
@props([
    'name', 'label' => null, 'type' => 'text', 'value' => null, 'required' => false, 'hint' => null, 'wrapper' => '', 'icon' => null,
    'after' => null, 'native' => false,
])

@php
    $key = str_replace(['[', ']'], ['.', ''], $name);
    $id = $attributes->get('id', $name);
    $current = $type === 'password' || $type === 'file' ? null : old($key, $value instanceof \DateTimeInterface ? $value->format($type === 'month' ? 'Y-m' : 'Y-m-d') : $value);
    $typeIcons = ['email' => 'envelope', 'tel' => 'phone', 'password' => 'lock', 'date' => 'calendar', 'month' => 'calendar', 'time' => 'clock', 'url' => 'link', 'search' => 'search'];
    $icon = $icon === false || $type === 'file' ? null : ($icon ?? $typeIcons[$type] ?? null);
    $isPassword = $type === 'password';
    $isPicker = in_array($type, ['date', 'month'], true) && ! $native;
    $hasError = $errors->has($key);

    // label shown before Alpine boots, in the same "09 Nov 2026" style the app prints dates in
    $initialLabel = null;
    if ($isPicker && filled($current)) {
        try {
            $initialLabel = $type === 'month'
                ? \Illuminate\Support\Carbon::createFromFormat('!Y-m', $current)->format('F Y')
                : \Illuminate\Support\Carbon::createFromFormat('!Y-m-d', $current)->format('d M Y');
        } catch (\Throwable) {
            $initialLabel = $current;
        }
    }
@endphp

<x-form.field :name="$name" :label="$label" :required="$required" :hint="$hint" :for="$id" :class="$wrapper">
    @if ($isPicker)
        <div x-data="datePicker({ mode: @js($type), after: @js($after) })" class="relative" @keydown.escape="open && ($event.preventDefault(), $event.stopPropagation(), close(true))">
            {{-- the real control: submitted with the form, invisible, sits under the button so browser validation points here --}}
            <input type="{{ $type }}" x-ref="native" name="{{ $name }}" id="{{ $id }}-native" tabindex="-1" aria-hidden="true"
                   @if ($current !== null) value="{{ $current }}" @endif @required($required)
                   {{ $attributes->except(['class', 'id', 'placeholder']) }}
                   class="pointer-events-none absolute inset-0 size-full opacity-0">

            <button type="button" id="{{ $id }}" x-ref="button" @click="toggle()" @keydown="onButtonKey($event)" :disabled="isDisabled"
                    aria-haspopup="dialog" :aria-expanded="open.toString()"
                    @class([
                        'relative flex w-full items-center gap-2 rounded-lg border bg-white py-2 pr-2 pl-3 text-left text-sm transition focus:outline-none disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-500',
                        'border-slate-300 focus:border-primary-500 focus:ring-0' => ! $hasError,
                        'border-red-400 focus:border-red-500 focus:ring-0' => $hasError,
                    ])
                    :class="open && '{{ $hasError ? 'border-red-500' : 'border-primary-500' }}'">
                @if ($icon)
                    <x-icon :name="$icon" @class(['size-4', 'text-slate-400' => ! $hasError, 'text-red-400' => $hasError]) />
                @endif
                <span class="min-w-0 flex-1 truncate" :class="value ? 'text-slate-900' : 'text-slate-400'" x-text="label || @js($attributes->get('placeholder', $type === 'month' ? 'Select month' : 'Select date'))"
                      @class(['text-slate-900' => $initialLabel, 'text-slate-400' => ! $initialLabel])>{{ $initialLabel ?? $attributes->get('placeholder', $type === 'month' ? 'Select month' : 'Select date') }}</span>
                <span x-show="value && ! isRequired" x-cloak role="button" tabindex="-1" @click.stop="clear()" title="Clear"
                      class="rounded p-0.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                    <x-icon name="x-mark" class="size-3.5" />
                </span>
                <x-icon name="chevron-down" class="size-4 text-slate-400 transition" ::class="open && 'rotate-180'" />
            </button>

            <template x-teleport="body">
                <div x-show="open" x-cloak x-ref="panel" tabindex="-1" role="dialog" aria-label="Choose a date" :style="panelStyle"
                     @keydown="onPanelKey($event)" @click.outside="if (! $refs.button.contains($event.target)) close()"
                     x-transition:enter="transition duration-100 ease-out" x-transition:enter-start="scale-95 opacity-0" x-transition:enter-end="scale-100 opacity-100"
                     x-transition:leave="transition duration-75 ease-in" x-transition:leave-end="opacity-0"
                     class="z-[70] rounded-xl bg-white p-3 text-sm shadow-lg ring-1 ring-slate-900/10 focus:outline-none">
                    {{-- header: previous / title (click to zoom out to months, then years) / next --}}
                    <div class="mb-2 flex items-center justify-between">
                        <button type="button" @click="step(-1)" class="grid size-8 place-items-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-700">
                            <span class="sr-only">Previous</span><x-icon name="chevron-right" class="size-4 rotate-180" />
                        </button>
                        <button type="button" @click="zoomOut()" :disabled="view === 'years'"
                                class="rounded-lg px-2.5 py-1 font-semibold text-slate-900 hover:bg-slate-100 disabled:hover:bg-transparent" x-text="heading"></button>
                        <button type="button" @click="step(1)" class="grid size-8 place-items-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-700">
                            <span class="sr-only">Next</span><x-icon name="chevron-right" class="size-4" />
                        </button>
                    </div>

                    {{-- days --}}
                    <div x-show="view === 'days'">
                        <div class="grid grid-cols-7 text-center text-[11px] font-semibold tracking-wide text-slate-400 uppercase">
                            <template x-for="weekday in ['Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su']"><span class="py-1" x-text="weekday"></span></template>
                        </div>
                        <div class="grid grid-cols-7 gap-0.5">
                            <template x-for="cell in days" :key="cell.iso">
                                <button type="button" @click="pick(cell.iso)" :disabled="cell.disabled" x-text="cell.day"
                                        class="grid h-9 place-items-center rounded-lg text-sm transition disabled:cursor-not-allowed disabled:text-slate-300 disabled:line-through"
                                        :class="{
                                            'bg-primary-600 font-semibold text-white hover:bg-primary-700': cell.iso === value,
                                            'text-slate-400': cell.outside && cell.iso !== value,
                                            'text-slate-700 hover:bg-slate-100': ! cell.outside && cell.iso !== value,
                                            'font-semibold text-primary-600 ring-1 ring-primary-200 ring-inset': cell.iso === today && cell.iso !== value,
                                            'ring-2 ring-primary-500 ring-inset': cell.iso === active && cell.iso !== value,
                                        }"></button>
                            </template>
                        </div>
                    </div>

                    {{-- months --}}
                    <div x-show="view === 'months'" class="grid grid-cols-3 gap-1.5">
                        <template x-for="m in months" :key="m.index">
                            <button type="button" @click="chooseMonth(m)" :disabled="m.disabled" x-text="m.name"
                                    class="rounded-lg py-2.5 text-sm transition disabled:cursor-not-allowed disabled:text-slate-300"
                                    :class="isSelectedMonth(m.index) ? 'bg-primary-600 font-semibold text-white' : (m.iso === today.slice(0, 7) ? 'font-semibold text-primary-600 ring-1 ring-primary-200 ring-inset hover:bg-slate-100' : 'text-slate-700 hover:bg-slate-100')"></button>
                        </template>
                    </div>

                    {{-- years --}}
                    <div x-show="view === 'years'" class="grid grid-cols-3 gap-1.5">
                        <template x-for="y in years" :key="y">
                            <button type="button" @click="chooseYear(y)" x-text="y"
                                    class="rounded-lg py-2.5 text-sm transition"
                                    :class="value.startsWith(y + '-') ? 'bg-primary-600 font-semibold text-white' : (today.startsWith(y + '-') ? 'font-semibold text-primary-600 ring-1 ring-primary-200 ring-inset hover:bg-slate-100' : 'text-slate-700 hover:bg-slate-100')"></button>
                        </template>
                    </div>

                    {{-- footer --}}
                    <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-2.5">
                        <button type="button" x-show="! isRequired" @click="clear()" class="rounded-md px-2 py-1 text-xs font-medium text-slate-500 hover:bg-slate-100 hover:text-slate-700">Clear</button>
                        <span x-show="isRequired"></span>
                        <button type="button" @click="pickToday()" :disabled="! todayAllowed"
                                class="rounded-md px-2 py-1 text-xs font-semibold text-primary-600 hover:bg-primary-50 disabled:cursor-not-allowed disabled:text-slate-300 disabled:hover:bg-transparent"
                                x-text="mode === 'month' ? 'This month' : 'Today'"></button>
                    </div>
                </div>
            </template>
        </div>
    @else
        <div class="relative" @if ($isPassword) x-data="{ reveal: false }" @endif>
            @if ($icon)
                <x-icon :name="$icon" @class(['pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2', 'text-slate-400' => ! $hasError, 'text-red-400' => $hasError]) />
            @endif

            <input type="{{ $type }}" name="{{ $name }}" id="{{ $id }}"
                   @if ($isPassword) x-bind:type="reveal ? 'text' : 'password'" @endif
                   @if ($current !== null) value="{{ $current }}" @endif
                   @required($required)
                   {{ $attributes->class([
                       'block w-full rounded-lg text-sm placeholder:text-slate-400',
                       'pl-9' => $icon,
                       'pr-10' => $isPassword,
                       'border-slate-300 focus:border-primary-500 focus:ring-0' => ! $hasError,
                       'border-red-400 text-red-900 focus:border-red-500 focus:ring-0' => $hasError,
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
    @endif
</x-form.field>
