{{-- Trash icon that opens a confirmation dialog and POSTs $fields to $action. --}}
@props(['action', 'fields' => [], 'title' => 'Delete record', 'message' => 'Are you sure you want to delete this record? This cannot be undone.'])

<div x-data="{ open: false }" class="inline-block">
    <x-icon-link icon="trash" label="Delete" tone="danger" @click="open = true" />

    <template x-teleport="body">
        <div x-cloak x-show="open" class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div x-show="open" x-transition.opacity class="absolute inset-0 bg-slate-900/50" @click="open = false"></div>
            <div x-show="open" x-transition @keydown.escape.window="open = false"
                 class="relative w-full max-w-md rounded-xl bg-white p-6 text-left shadow-xl">
                <div class="flex gap-4">
                    <span class="grid size-10 shrink-0 place-items-center rounded-full bg-red-100 text-red-600">
                        <x-icon name="warning" />
                    </span>
                    <div>
                        <h3 class="text-base font-semibold text-slate-900">{{ $title }}</h3>
                        <p class="mt-1 text-sm whitespace-normal text-slate-500">{{ $message }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ $action }}" class="mt-6 flex justify-end gap-2">
                    @csrf
                    @foreach ($fields as $name => $value)
                        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                    @endforeach
                    <x-button variant="secondary" type="button" @click="open = false">Cancel</x-button>
                    <x-button variant="danger">Delete</x-button>
                </form>
            </div>
        </div>
    </template>
</div>
