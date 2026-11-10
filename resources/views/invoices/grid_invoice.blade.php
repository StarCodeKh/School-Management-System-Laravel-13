<x-layouts.app title="Invoices">
    <x-page-header title="Invoices" :breadcrumbs="['Dashboard' => route('home'), 'Invoices' => route('invoice/list/page'), 'Grid' => null]">
        <x-slot:actions>
            <x-button :href="route('invoice/add/page')" icon="plus">New invoice</x-button>
        </x-slot:actions>
    </x-page-header>

    @include('invoices._tabs', ['tab' => null])

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @forelse ($invoiceList as $invoice)
            <div class="flex flex-col rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
                    <a href="{{ route('invoice/view/page', $invoice) }}" class="font-semibold text-primary-600 hover:text-primary-700">{{ $invoice->invoice_number }}</a>
                    <x-invoice-status :invoice="$invoice" />
                </div>
                <div class="flex items-center gap-3 px-5 py-4">
                    <x-avatar :src="$invoice->customer?->avatar_url ?? asset('images/photo_defaults.jpg')" :alt="$invoice->customer_name" />
                    <span class="truncate font-medium text-slate-900">{{ $invoice->customer_name }}</span>
                </div>
                <div class="mt-auto grid grid-cols-2 gap-2 border-t border-slate-100 px-5 py-3 text-sm">
                    <div><p class="text-xs text-slate-500">Amount</p><p class="font-semibold text-slate-900">{{ money($invoice->total_amount) }}</p></div>
                    <div class="text-right"><p class="text-xs text-slate-500">Due</p><p class="font-medium text-slate-900">{{ $invoice->due_date->format('d M Y') }}</p></div>
                </div>
                <div class="border-t border-slate-100 px-3 py-2 text-right">@include('invoices._actions')</div>
            </div>
        @empty
            <x-empty-state icon="document" class="col-span-full">No invoices yet.</x-empty-state>
        @endforelse
    </div>

    <div class="mt-6">{{ $invoiceList->links() }}</div>
</x-layouts.app>
