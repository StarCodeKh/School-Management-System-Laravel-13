{{-- toast for session('success'|'error'|'warning'|'info') messages --}}
@php
    $messages = collect(['success', 'error', 'warning', 'info'])
        ->filter(fn ($type) => session()->has($type))
        ->map(fn ($type) => ['type' => $type, 'text' => session($type)])
        ->values();
    if ($errors->any() && ! session()->has('error')) {
        $messages->push(['type' => 'error', 'text' => 'Please correct the highlighted fields.']);
    }
@endphp

@if ($messages->isNotEmpty())
    <div class="pointer-events-none fixed inset-x-0 top-4 z-50 flex flex-col items-center gap-2 px-4 sm:items-end sm:pr-6">
        @foreach ($messages as $message)
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-transition
                 role="alert" @class([
                     'pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-lg border bg-white p-4 shadow-lg',
                     'border-emerald-200' => $message['type'] === 'success',
                     'border-red-200' => $message['type'] === 'error',
                     'border-amber-200' => $message['type'] === 'warning',
                     'border-sky-200' => $message['type'] === 'info',
                 ])>
                <x-icon :name="['success' => 'check-circle', 'error' => 'x-circle', 'warning' => 'warning', 'info' => 'information'][$message['type']]"
                        :class="'size-5 '.['success' => 'text-emerald-500', 'error' => 'text-red-500', 'warning' => 'text-amber-500', 'info' => 'text-sky-500'][$message['type']]" />
                <p class="flex-1 text-sm text-slate-700">{{ $message['text'] }}</p>
                <button type="button" class="text-slate-400 hover:text-slate-600" @click="show = false">
                    <span class="sr-only">Dismiss</span>
                    <x-icon name="x-mark" class="size-4" />
                </button>
            </div>
        @endforeach
    </div>
@endif
