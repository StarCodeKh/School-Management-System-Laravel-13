<x-layouts.app title="Fee Structures">
    <x-page-header title="Fee Structures" :breadcrumbs="['Finance' => route('finance.index'), 'Fee Structures' => null]" />

    <x-card title="Set a fee for a class" class="mb-6">
        <form method="POST" action="{{ route('fee-structures.store') }}" class="grid gap-5 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_10rem_11rem_auto] lg:items-start">
            @csrf
            <x-form.select name="school_class_id" label="Class" :options="$classes->pluck('name', 'id')->map(fn ($n) => 'Class '.$n)" placeholder="Select class" required />
            <x-form.select name="fee_type_id" label="Fee type" :options="$feeTypes" placeholder="Select fee type" required />
            <x-form.input name="amount" type="number" step="0.01" min="0" :label="'Amount ('.setting('currency_symbol').')'" required />
            <x-form.input name="due_date" type="date" label="Due date" />
            <x-button icon="check" class="lg:mt-7">Save</x-button>
        </form>
        <p class="mt-3 text-xs text-slate-500">Saving a class and fee type that already exist updates the amount.</p>
    </x-card>

    <div class="grid gap-6 lg:grid-cols-2 xl:grid-cols-3">
        @forelse ($classes as $class)
            <x-card :title="'Class '.$class->name" :padding="false">
                <x-slot:actions>
                    <span class="text-sm font-semibold text-slate-900">{{ money($class->feeStructures->sum('amount')) }}</span>
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
            <p class="col-span-full text-sm text-slate-500">No classes yet.</p>
        @endforelse
    </div>
</x-layouts.app>
