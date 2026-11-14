{{--
    Toast stack. Seeds itself from session('success'|'error'|'warning'|'info'|'status') and the
    validation bag; the toasts themselves live in the Alpine "toasts" store (resources/js/app.js),
    so pages can also raise them from JavaScript with window.toast() or $dispatch('toast', {...}).
--}}
@php
    $flashes = collect(['success', 'error', 'warning', 'info'])
        ->filter(fn ($type) => session()->has($type))
        ->map(fn ($type) => ['type' => $type, 'message' => (string) session($type)])
        ->values();

    // Laravel's auth scaffolding (password reset, verification) flashes "status"
    if (session()->has('status')) {
        $flashes->push(['type' => 'success', 'message' => (string) session('status')]);
    }

    if ($errors->any() && ! session()->has('error')) {
        $flashes->push([
            'type' => 'error',
            'title' => 'Please check the form',
            'message' => $errors->count() === 1 ? $errors->first() : $errors->count().' fields need attention.',
            'details' => $errors->count() > 1 ? array_slice($errors->all(), 0, 3) : [],
        ]);
    }

    $styles = [
        'success' => ['icon' => 'check-circle', 'badge' => 'bg-emerald-50 text-emerald-600', 'bar' => 'bg-emerald-500'],
        'error' => ['icon' => 'x-circle', 'badge' => 'bg-red-50 text-red-600', 'bar' => 'bg-red-500'],
        'warning' => ['icon' => 'warning', 'badge' => 'bg-amber-50 text-amber-600', 'bar' => 'bg-amber-500'],
        'info' => ['icon' => 'information', 'badge' => 'bg-sky-50 text-sky-600', 'bar' => 'bg-sky-500'],
    ];
@endphp

<div x-data x-init="@js($flashes).forEach((flash) => $store.toasts.push(flash))"
     aria-live="polite" aria-atomic="false"
     class="pointer-events-none fixed inset-x-0 top-3 z-[90] flex flex-col items-center gap-2 px-4 sm:items-end sm:pr-5">
    <template x-for="toast in $store.toasts.items" :key="toast.id">
        <div x-show="toast.visible"
             x-transition:enter="transition duration-200 ease-out"
             x-transition:enter-start="-translate-y-2 scale-95 opacity-0"
             x-transition:enter-end="translate-y-0 scale-100 opacity-100"
             x-transition:leave="transition duration-150 ease-in"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             @mouseenter="$store.toasts.pause(toast.id)" @mouseleave="$store.toasts.resume(toast.id)"
             @focusin="$store.toasts.pause(toast.id)" @focusout="$store.toasts.resume(toast.id)"
             :role="toast.type === 'error' ? 'alert' : 'status'"
             class="pointer-events-auto relative w-full max-w-xs overflow-hidden rounded-lg bg-white shadow-md ring-1 ring-slate-900/10">
            <div class="flex items-center gap-2.5 py-2 pr-2 pl-2.5">
                @foreach ($styles as $type => $style)
                    <template x-if="toast.type === '{{ $type }}'">
                        <span class="grid size-6 shrink-0 place-items-center self-start rounded-full {{ $style['badge'] }}">
                            <x-icon :name="$style['icon']" class="size-4" />
                        </span>
                    </template>
                @endforeach

                <div class="min-w-0 flex-1 py-0.5 text-[13px] leading-5">
                    <p class="font-semibold text-slate-900" x-show="toast.title" x-text="toast.title"></p>
                    <p class="break-words text-slate-700" x-show="toast.message" x-text="toast.message"></p>
                    <ul class="mt-0.5 list-disc pl-4 text-xs text-slate-500" x-show="toast.details.length">
                        <template x-for="detail in toast.details"><li x-text="detail"></li></template>
                    </ul>
                </div>

                <button type="button" class="shrink-0 self-start rounded p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600 focus-visible:outline-2 focus-visible:outline-primary-500"
                        @click="$store.toasts.dismiss(toast.id)">
                    <span class="sr-only">Dismiss</span>
                    <x-icon name="x-mark" class="size-3.5" />
                </button>
            </div>

            {{-- countdown bar; it freezes while the toast is hovered or focused --}}
            <div x-show="toast.timeout > 0" class="absolute inset-x-0 bottom-0 h-0.5">
                <div class="h-full origin-left opacity-70"
                     :class="{ @foreach ($styles as $type => $style)'{{ $style['bar'] }}': toast.type === '{{ $type }}',@endforeach }"
                     :style="`animation: toast-countdown ${toast.timeout}ms linear forwards; animation-play-state: ${toast.timer ? 'running' : 'paused'}`"></div>
            </div>
        </div>
    </template>
</div>
