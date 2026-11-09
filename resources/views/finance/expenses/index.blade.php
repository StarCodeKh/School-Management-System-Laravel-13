<x-layouts.app title="Expenses">
    <x-page-header title="Expenses" :breadcrumbs="['Finance' => route('finance.index'), 'Expenses' => null]">
        <x-slot:actions>
            <x-button :href="route('expenses.create')" icon="plus">Add expense</x-button>
        </x-slot:actions>
    </x-page-header>

    <form method="GET" class="mb-4 flex flex-wrap items-center gap-2">
        <x-form.input name="month" type="month" :value="$month" wrapper="w-56" />
        <x-form.select name="category" :options="\App\Enums\ExpenseCategory::options()" :value="$filters['category'] ?? null"
                       placeholder="All categories" icon="tag" wrapper="sm:w-52" />
        <x-button variant="secondary">Filter</x-button>
        <span class="ml-auto text-sm text-slate-600">Total: <span class="font-semibold text-slate-900">{{ money($total) }}</span></span>
    </form>

    <x-table>
        <x-slot:head>
            <tr><th>Date</th><th>Title</th><th>Category</th><th>Paid to</th><th>Method</th><th class="text-right">Amount</th><th class="text-right">Actions</th></tr>
        </x-slot:head>
        @forelse ($expenses as $expense)
            <tr class="hover:bg-slate-50">
                <td>{{ $expense->expense_date->format('d M Y') }}</td>
                <td>
                    <p class="font-medium text-slate-900">{{ $expense->title }}</p>
                    @if ($expense->reference)<p class="text-xs text-slate-500">Ref: {{ $expense->reference }}</p>@endif
                </td>
                <td><x-badge color="amber">{{ $expense->category->label() }}</x-badge></td>
                <td>{{ $expense->paid_to ?: '-' }}</td>
                <td>{{ $expense->payment_method->label() }}</td>
                <td class="text-right font-semibold text-slate-900">{{ money($expense->amount) }}</td>
                <td class="text-right">
                    <x-icon-link :href="route('expenses.edit', $expense)" icon="pencil" label="Edit" tone="primary" />
                    <x-delete-button :action="route('expenses.destroy', $expense)" :fields="['_method' => 'DELETE']" title="Delete expense" :message="'Delete '.$expense->title.'?'" />
                </td>
            </tr>
        @empty
            <x-empty-row colspan="7" message="No expenses for this month." />
        @endforelse
        <x-slot:footer>{{ $expenses->links() }}</x-slot:footer>
    </x-table>
</x-layouts.app>
