{{--
    The one dialog shell for the app (modal in resources/js/app.js).
    <x-button x-data @click="$dispatch('open-modal', 'add-route')" icon="plus">Add route</x-button>
    <x-modal name="add-route" title="Add route" description="..." icon="truck">
        <form method="POST" action="..." id="add-route-form">@csrf <x-modal.form-name name="add-route" /> ...fields... </form>
        <x-slot:footer><x-button form="add-route-form">Save</x-button></x-slot:footer>
    </x-modal>
    Open with fields filled in: $dispatch('open-modal', { name: 'add-period', fill: { day_of_week: 1, period: 3 } }).
    It reopens by itself after a failed save when its form carries <x-modal.form-name>, so the errors are shown in place.
    Esc, the backdrop and the X close it; Tab stays inside; focus returns to the button that opened it.
    size: sm | md | lg
--}}
@props(['name', 'title', 'description' => null, 'icon' => null, 'size' => 'md'])

@php
    $width = ['sm' => 'sm:max-w-md', 'md' => 'sm:max-w-lg', 'lg' => 'sm:max-w-2xl'][$size] ?? 'sm:max-w-lg';
    $reopen = old('_modal') === $name && $errors->any();
@endphp

<div x-data="modal({ name: @js($name), open: @js($reopen) })" @keydown.escape.window="open && hide()">
    <template x-teleport="body">
        <div x-show="open" x-cloak class="fixed inset-0 z-[75] flex items-end justify-center sm:items-center sm:p-4"
             role="dialog" aria-modal="true" aria-labelledby="modal-{{ $name }}-title" @keydown.tab="trap($event)">
            <div x-show="open" x-transition.opacity.duration.200ms class="absolute inset-0 bg-slate-900/50 backdrop-blur-[2px]" @click="hide()"></div>

            <div x-show="open" x-ref="panel"
                 x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="translate-y-8 opacity-0 sm:translate-y-2 sm:scale-95" x-transition:enter-end="translate-y-0 scale-100 opacity-100"
                 x-transition:leave="transition duration-150 ease-in" x-transition:leave-end="translate-y-8 opacity-0 sm:translate-y-0 sm:scale-95"
                 {{ $attributes->class(['relative flex max-h-[92vh] w-full flex-col overflow-hidden rounded-t-2xl bg-white shadow-xl ring-1 ring-slate-900/5 sm:rounded-2xl', $width]) }}>
                {{-- header --}}
                <div class="flex items-start gap-3 border-b border-slate-100 px-5 py-4 sm:px-6">
                    @if ($icon)
                        <span class="grid size-10 shrink-0 place-items-center rounded-full bg-primary-50 text-primary-600"><x-icon :name="$icon" class="size-5" /></span>
                    @endif
                    <div class="min-w-0 flex-1 pt-0.5">
                        <h2 id="modal-{{ $name }}-title" class="text-base font-semibold text-slate-900">{{ $title }}</h2>
                        @if ($description)
                            <p class="mt-0.5 text-sm text-slate-500">{{ $description }}</p>
                        @endif
                    </div>
                    <button type="button" @click="hide()" class="-m-1 rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 focus-visible:outline-2 focus-visible:outline-primary-500">
                        <span class="sr-only">Close</span><x-icon name="x-mark" class="size-5" />
                    </button>
                </div>

                {{-- body --}}
                <div x-ref="body" class="overflow-y-auto px-5 py-5 sm:px-6">
                    {{ $slot }}
                </div>

                @isset($footer)
                    <div class="flex flex-col-reverse gap-2 border-t border-slate-100 bg-slate-50 px-5 py-3.5 sm:flex-row sm:justify-end sm:px-6">
                        <x-button type="button" variant="secondary" @click="hide()">Cancel</x-button>
                        {{ $footer }}
                    </div>
                @endisset
            </div>
        </div>
    </template>
</div>
