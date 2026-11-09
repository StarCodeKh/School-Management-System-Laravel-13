{{-- The one confirmation dialog for the whole app, driven by the Alpine "confirm" store (resources/js/app.js). --}}
<div x-data x-cloak x-show="$store.confirm.open" class="fixed inset-0 z-[80] flex items-end justify-center p-4 sm:items-center"
     role="alertdialog" aria-modal="true" aria-labelledby="confirm-title" aria-describedby="confirm-message"
     @keydown.escape.window="$store.confirm.open && $store.confirm.answer(false)">
    <div x-show="$store.confirm.open" x-transition.opacity class="absolute inset-0 bg-slate-900/50 backdrop-blur-[2px]" @click="$store.confirm.answer(false)"></div>

    <div x-show="$store.confirm.open"
         x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="translate-y-4 scale-95 opacity-0" x-transition:enter-end="translate-y-0 scale-100 opacity-100"
         x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="scale-95 opacity-0"
         class="relative w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-xl ring-1 ring-slate-900/5">
        <div class="flex gap-4 p-6">
            <span class="grid size-11 shrink-0 place-items-center rounded-full"
                  :class="$store.confirm.tone === 'danger' ? 'bg-red-100 text-red-600' : 'bg-primary-100 text-primary-600'">
                <template x-if="$store.confirm.tone === 'danger'"><x-icon name="warning" /></template>
                <template x-if="$store.confirm.tone !== 'danger'"><x-icon name="information" /></template>
            </span>
            <div class="min-w-0 pt-0.5">
                <h3 id="confirm-title" class="text-base font-semibold text-slate-900" x-text="$store.confirm.title"></h3>
                <p id="confirm-message" class="mt-1 text-sm break-words text-slate-500" x-text="$store.confirm.message"></p>
            </div>
        </div>
        <div class="flex flex-col-reverse gap-2 bg-slate-50 px-6 py-4 sm:flex-row sm:justify-end">
            <x-button type="button" variant="secondary" @click="$store.confirm.answer(false)">Cancel</x-button>
            <button type="button" x-ref="ok" x-init="$watch('$store.confirm.open', (open) => open && $nextTick(() => $el.focus()))"
                    @click="$store.confirm.answer(true)" x-text="$store.confirm.confirmLabel"
                    class="inline-flex h-9.5 items-center justify-center rounded-lg px-4 text-sm font-semibold text-white shadow-sm ring-1 ring-inset transition active:scale-[.97] focus-visible:outline-2 focus-visible:outline-offset-2"
                    :class="$store.confirm.tone === 'danger'
                        ? 'bg-linear-to-b from-red-500 to-red-600 ring-red-700/20 hover:from-red-600 hover:to-red-700 focus-visible:outline-red-600'
                        : 'bg-linear-to-b from-primary-500 to-primary-600 ring-primary-700/20 hover:from-primary-600 hover:to-primary-700 focus-visible:outline-primary-600'"></button>
        </div>
    </div>
</div>
