<x-layouts.app :title="$invoice->invoice_number">
    <x-page-header :title="'Invoice '.$invoice->invoice_number" :breadcrumbs="['Invoices' => route('invoice/list/page'), $invoice->invoice_number => null]">
        <x-slot:actions>
            <x-button variant="secondary" icon="printer" type="button" onclick="window.print()">Print</x-button>
            <x-button :href="route('invoice/edit/page', $invoice)" icon="pencil">Edit</x-button>
        </x-slot:actions>
    </x-page-header>

    <article class="mx-auto max-w-4xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm sm:p-10 print:border-0 print:shadow-none">
        <header class="flex flex-col gap-6 border-b border-slate-200 pb-6 sm:flex-row sm:justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <span class="grid size-10 place-items-center rounded-lg bg-primary-600 text-white"><x-icon name="academic-cap" /></span>
                    <span class="text-lg font-bold text-slate-900">{{ setting('school_name') }}</span>
                </div>
                <p class="mt-3 text-sm whitespace-pre-line text-slate-500">{{ $invoice->invoice_from }}</p>
            </div>
            <div class="sm:text-right">
                <p class="text-2xl font-bold text-slate-900">INVOICE</p>
                <p class="text-sm text-slate-500">{{ $invoice->invoice_number }}</p>
                <div class="mt-2"><x-invoice-status :invoice="$invoice" /></div>
            </div>
        </header>

        <section class="grid gap-6 border-b border-slate-200 py-6 text-sm sm:grid-cols-3">
            <div>
                <p class="text-xs font-semibold tracking-wide text-slate-500 uppercase">Billed to</p>
                <p class="mt-1 font-semibold text-slate-900">{{ $invoice->customer_name }}</p>
                <p class="whitespace-pre-line text-slate-500">{{ $invoice->invoice_to }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold tracking-wide text-slate-500 uppercase">Dates</p>
                <p class="mt-1 text-slate-700">Issued: {{ $invoice->invoice_date?->format('d M Y') }}</p>
                <p class="text-slate-700">Due: {{ $invoice->due_date->format('d M Y') }}</p>
                <p class="text-slate-700">PO: {{ $invoice->po_number }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold tracking-wide text-slate-500 uppercase">Payment</p>
                <p class="mt-1 text-slate-700">{{ $invoice->bank_name ?: '-' }}</p>
                <p class="text-slate-700">{{ $invoice->account_holder_name }}</p>
                <p class="text-slate-700">{{ $invoice->account_number }}</p>
                @if ($invoice->ifsc_code)<p class="text-slate-700">Code: {{ $invoice->ifsc_code }}</p>@endif
            </div>
        </section>

        <div class="overflow-x-auto py-6">
            <table class="min-w-full text-sm">
                <thead class="border-b border-slate-200 text-left text-xs font-semibold tracking-wide text-slate-500 uppercase">
                    <tr><th class="py-2 pr-4">Item</th><th class="py-2 pr-4">Category</th><th class="py-2 pr-4 text-right">Qty</th><th class="py-2 pr-4 text-right">Price</th><th class="py-2 pr-4 text-right">Discount</th><th class="py-2 text-right">Amount</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($invoice->items as $item)
                        <tr>
                            <td class="py-3 pr-4 font-medium text-slate-900">{{ $item->item }}</td>
                            <td class="py-3 pr-4 text-slate-500">{{ $item->category }}</td>
                            <td class="py-3 pr-4 text-right">{{ rtrim(rtrim($item->quantity, '0'), '.') }}</td>
                            <td class="py-3 pr-4 text-right">{{ money($item->price) }}</td>
                            <td class="py-3 pr-4 text-right">{{ (float) $item->discount }}%</td>
                            <td class="py-3 text-right font-medium text-slate-900">{{ money($item->amount) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex flex-col gap-6 border-t border-slate-200 pt-6 sm:flex-row sm:justify-between">
            <div class="space-y-4 text-sm sm:max-w-sm">
                @if ($invoice->notes)
                    <div><p class="font-semibold text-slate-900">Notes</p><p class="whitespace-pre-line text-slate-500">{{ $invoice->notes }}</p></div>
                @endif
                @if ($invoice->terms_and_conditions)
                    <div><p class="font-semibold text-slate-900">Terms and conditions</p><p class="whitespace-pre-line text-slate-500">{{ $invoice->terms_and_conditions }}</p></div>
                @endif
            </div>

            <dl class="w-full space-y-2 text-sm sm:w-72">
                <div class="flex justify-between"><dt class="text-slate-500">Subtotal</dt><dd>{{ money($invoice->taxable_amount) }}</dd></div>
                @if ($invoice->enable_tax)
                    <div class="flex justify-between"><dt class="text-slate-500">{{ setting('tax_name', 'Tax') }} ({{ (float) $invoice->tax_rate }}%)</dt><dd>{{ money($invoice->tax_amount) }}</dd></div>
                @endif
                @foreach ($invoice->additional_charges ?? [] as $charge)
                    <div class="flex justify-between"><dt class="text-slate-500">Additional charge</dt><dd>{{ money($charge) }}</dd></div>
                @endforeach
                @foreach ($invoice->discounts ?? [] as $discount)
                    <div class="flex justify-between"><dt class="text-slate-500">Discount ({{ (float) $discount }}%)</dt><dd>-{{ money($invoice->taxable_amount * $discount / 100) }}</dd></div>
                @endforeach
                @if ((float) $invoice->round_off)
                    <div class="flex justify-between"><dt class="text-slate-500">Round off</dt><dd>{{ money($invoice->round_off) }}</dd></div>
                @endif
                <div class="flex justify-between border-t border-slate-200 pt-2 text-base font-bold text-slate-900"><dt>Total</dt><dd>{{ money($invoice->total_amount) }}</dd></div>
            </dl>
        </div>

        <footer class="mt-10 flex justify-end">
            <div class="text-center">
                @if ($invoice->signature_path)
                    <img src="{{ asset($invoice->signature_path) }}" alt="Signature" class="mx-auto h-16 w-auto">
                @endif
                <p class="mt-2 border-t border-slate-300 pt-2 text-sm font-semibold text-slate-900">{{ $invoice->signatory_name }}</p>
            </div>
        </footer>
    </article>
</x-layouts.app>
