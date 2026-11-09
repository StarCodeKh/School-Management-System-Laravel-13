<x-layouts.app title="Invoices">
    <x-page-header title="Invoices" :breadcrumbs="['Dashboard' => route('home'), 'Invoices' => null]">
        <x-slot:actions>
            @can('manage-settings')
                <x-button variant="secondary" :href="route('invoice/settings/page')" icon="cog">Settings</x-button>
            @endcan
            <x-button :href="route('invoice/add/page')" icon="plus">New invoice</x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat-card :label="'All invoices ('.$stats['all']['count'].')'" :value="money($stats['all']['amount'])" icon="document" />
        <x-stat-card :label="'Paid ('.$stats['paid']['count'].')'" :value="money($stats['paid']['amount'])" icon="check-circle" color="green" />
        <x-stat-card :label="'Unpaid ('.$stats['unpaid']['count'].')'" :value="money($stats['unpaid']['amount'])" icon="clipboard" color="amber" />
        <x-stat-card :label="'Cancelled ('.$stats['cancelled']['count'].')'" :value="money($stats['cancelled']['amount'])" icon="x-circle" color="red" />
    </div>

    @include('invoices._tabs')

    {{-- filters --}}
    <form method="GET" data-live x-data="{ open: @js(request()->hasAny(['customers', 'categories', 'from', 'to'])) }" class="mb-4">
        <div class="flex items-center gap-2">
            <x-button type="button" variant="secondary" size="sm" icon="search" @click="open = !open">Filters</x-button>
            @if (request()->hasAny(['customers', 'categories', 'from', 'to']))
                <x-button variant="ghost" size="sm" :href="url()->current()">Clear filters</x-button>
            @endif
        </div>
        <div x-show="open" x-cloak class="mt-3 grid gap-5 rounded-xl border border-slate-200 bg-white p-5 shadow-sm md:grid-cols-2 xl:grid-cols-4">
            <x-form.field label="Customers">
                <div class="flex max-h-40 flex-col gap-1.5 overflow-y-auto rounded-lg border border-slate-200 p-2.5">
                    @forelse ($customers as $customer)
                        <x-form.checkbox name="customers[]" :value="$customer->id" :checked="in_array($customer->id, array_map('intval', $filters['customers'] ?? []), true)" class="w-full">{{ $customer->name }}</x-form.checkbox>
                    @empty
                        <p class="text-sm text-slate-500">No customers yet.</p>
                    @endforelse
                </div>
            </x-form.field>
            <x-form.field label="Categories">
                <div class="flex max-h-40 flex-col gap-1.5 overflow-y-auto rounded-lg border border-slate-200 p-2.5">
                    @forelse ($categories as $category)
                        <x-form.checkbox name="categories[]" :value="$category" :checked="in_array($category, $filters['categories'] ?? [], true)">{{ $category }}</x-form.checkbox>
                    @empty
                        <p class="text-sm text-slate-500">No categories yet.</p>
                    @endforelse
                </div>
            </x-form.field>
            <div class="space-y-3">
                <x-form.input name="from" type="date" label="Invoice date from" :value="$filters['from'] ?? null" />
                <x-form.input name="to" type="date" after="from" label="Invoice date to" :value="$filters['to'] ?? null" />
            </div>
            <div class="flex items-end"><x-button class="w-full">Apply filters</x-button></div>
        </div>
    </form>

    <x-table live>
        <x-slot:head>
            <tr><x-th sort="invoice">Invoice</x-th><th>Category</th><x-th sort="created">Created</x-th><x-th sort="customer">Customer</x-th><x-th sort="amount">Amount</x-th><x-th sort="due_date">Due date</x-th><x-th sort="status">Status</x-th><th class="text-right">Actions</th></tr>
        </x-slot:head>
        @forelse ($invoiceList as $invoice)
            <tr class="hover:bg-slate-50">
                <td><a href="{{ route('invoice/view/page', $invoice) }}" class="font-semibold text-primary-600 hover:text-primary-700">{{ $invoice->invoice_number }}</a></td>
                <td>{{ $invoice->items->first()?->category }}</td>
                <td>{{ $invoice->created_at->format('d M Y') }}</td>
                <td>
                    <div class="flex items-center gap-3">
                        <x-avatar :src="$invoice->customer?->avatar_url ?? asset('images/photo_defaults.jpg')" :alt="$invoice->customer_name" size="size-8" />
                        <span class="font-medium text-slate-900">{{ $invoice->customer_name }}</span>
                    </div>
                </td>
                <td class="font-semibold text-slate-900">{{ money($invoice->total_amount) }}</td>
                <td>{{ $invoice->due_date->format('d M Y') }}</td>
                <td><x-invoice-status :invoice="$invoice" /></td>
                <td class="text-right">@include('invoices._actions')</td>
            </tr>
        @empty
            <x-empty-row colspan="8" message="No invoices found." />
        @endforelse
        <x-slot:footer>{{ $invoiceList->links() }}</x-slot:footer>
    </x-table>
</x-layouts.app>
