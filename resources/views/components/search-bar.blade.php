{{-- GET search form for list pages. Extra filters (e.g. a class select) go in the slot. --}}
@props(['value' => '', 'placeholder' => 'Search...'])

<form method="GET" data-live class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center">
    <div class="relative flex-1 sm:max-w-sm sm:min-w-72">
        <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400" />
        <input type="search" name="search" value="{{ $value }}" placeholder="{{ $placeholder }}"
               class="block w-full rounded-lg border-slate-300 py-2 pl-9 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500">
    </div>
    {{ $slot }}
    <x-button variant="secondary">Search</x-button>
    @if (request()->hasAny(['search', 'class']) && request()->query())
        <x-button variant="ghost" :href="url()->current()">Clear</x-button>
    @endif
</form>
