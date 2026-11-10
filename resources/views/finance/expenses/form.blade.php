<x-layouts.app :title="$expense ? 'Edit Expense' : 'Add Expense'">
    <x-page-header :title="$expense ? 'Edit Expense' : 'Add Expense'" :breadcrumbs="['Expenses' => route('expenses.index'), ($expense ? $expense->title : 'Add Expense') => null]" />

    <x-card class="max-w-3xl">
        <form method="POST" action="{{ $expense ? route('expenses.update', $expense) : route('expenses.store') }}" class="space-y-6">
            @csrf
            @if ($expense) @method('PUT') @endif
            <x-form.section title="Expense" description="What was paid for and how much." icon="banknotes">
                <x-form.input name="title" label="Title" :value="$expense?->title" required wrapper="sm:col-span-2" placeholder="e.g. Electricity bill" />
                <x-form.select name="category" label="Category" :options="\App\Enums\ExpenseCategory::options()" :value="$expense?->category" placeholder="Select category" required />
                <x-form.input name="amount" type="number" step="0.01" min="0.01" :label="'Amount ('.setting('currency_symbol').')'" :value="$expense?->amount" required />
                <x-form.input name="expense_date" type="date" label="Date" :value="$expense?->expense_date ?? today()" :max="today()->toDateString()" required />
            </x-form.section>

            <x-form.section title="Payment" description="How it was paid, for the audit trail." icon="currency">
                <x-form.select name="payment_method" label="Payment method" :options="\App\Enums\PaymentMethod::options()" :value="$expense?->payment_method" placeholder="Select method" required />
                <x-form.input name="paid_to" label="Paid to" :value="$expense?->paid_to" />
                <x-form.input name="reference" label="Reference" :value="$expense?->reference" placeholder="Receipt or transfer no." />
                <x-form.textarea name="notes" label="Notes" :value="$expense?->notes" wrapper="sm:col-span-2" />
            </x-form.section>
            <x-form.actions :submit="$expense ? 'Save changes' : 'Save expense'" :cancel="route('expenses.index')" />
        </form>
    </x-card>
</x-layouts.app>
