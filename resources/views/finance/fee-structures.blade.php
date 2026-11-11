<x-layouts.app title="Fee Structures">
    <x-page-header title="Fee Structures" :breadcrumbs="['Finance' => route('finance.index'), 'Fee Structures' => null]">
        <x-slot:actions>
            <x-button type="button" icon="plus" x-data @click="$dispatch('open-modal', 'set-fee')">Set a fee</x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="grid gap-6 lg:grid-cols-2 xl:grid-cols-3">
        @forelse ($classes as $class)
            <x-card :title="'Class '.$class->name" :padding="false">
                <x-slot:actions>
                    <span class="text-sm font-semibold text-slate-900">{{ money($class->feeStructures->sum('amount')) }}</span>
                    <x-icon-link icon="plus" :label="'Set a fee for class '.$class->name" tone="primary" x-data
                                 @click="$dispatch('open-modal', { name: 'set-fee', fill: { school_class_id: {{ $class->id }} } })" />
                </x-slot:actions>
                <ul class="divide-y divide-slate-100">
                    @forelse ($class->feeStructures->sortBy('feeType.name') as $structure)
                        <li class="flex items-center gap-3 px-5 py-2.5 text-sm">
                            <span class="flex-1 text-slate-700">{{ $structure->feeType?->name }}</span>
                            @if ($structure->due_date)<span class="text-xs text-slate-400">due {{ $structure->due_date->format('d M') }}</span>@endif
                            <span class="font-medium text-slate-900">{{ money($structure->amount) }}</span>
                            <x-delete-button :action="route('fee-structures.destroy', $structure)" :fields="['_method' => 'DELETE']" title="Remove fee"
                                             :message="'Remove '.$structure->feeType?->name.' from class '.$class->name.'?'" />
                        </li>
                    @empty
                        <li class="px-5 py-6 text-center text-sm text-slate-500">No fees set.</li>
                    @endforelse
                </ul>
            </x-card>
        @empty
            <x-empty-state icon="squares" class="col-span-full">No classes yet. Add classes on the Classes page first.</x-empty-state>
        @endforelse
    </div>

    <x-modal name="set-fee" title="Set a fee" description="Saving a class and fee type that already exist updates the amount." icon="banknotes">
        <form method="POST" action="{{ route('fee-structures.store') }}" id="set-fee-form" class="grid gap-5 sm:grid-cols-2">
            @csrf
            <x-modal.form-name name="set-fee" />
            <x-form.select name="school_class_id" label="Class" :options="$classes->pluck('name', 'id')->map(fn ($n) => 'Class '.$n)" placeholder="Select class" required icon="academic-cap" />
            <x-form.select name="fee_type_id" label="Fee type" :options="$feeTypes" placeholder="Select fee type" required icon="tag" />
            <x-form.input name="amount" type="number" step="0.01" min="0" :label="'Amount ('.setting('currency_symbol').')'" required />
            <x-form.input name="due_date" type="date" label="Due date" />
        </form>
        <x-slot:footer><x-button form="set-fee-form" icon="check">Save fee</x-button></x-slot:footer>
    </x-modal>
</x-layouts.app>
