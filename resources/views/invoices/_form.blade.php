{{--
    Invoice form shared by invoice_add and invoice_edit ($invoice is null when adding).
    Line items, charges and discounts are managed by the Alpine "invoiceForm" component in resources/js/app.js.
--}}
@php
    $initial = [
        'items' => old('items', $invoice?->items->map->only(['item', 'category', 'quantity', 'price', 'discount'])->all() ?? []),
        'charges' => old('charges', $invoice->additional_charges ?? []),
        'discounts' => old('discounts', $invoice->discounts ?? []),
        'enableTax' => (bool) old('enable_tax', $invoice->enable_tax ?? false),
        'taxRate' => (float) ($invoice?->enable_tax ? $invoice->tax_rate : setting('tax_rate', 0)),
        'roundOff' => old('round_off', $invoice->round_off ?? 0),
        'isRecurring' => (bool) old('is_recurring', $invoice->is_recurring ?? false),
    ];
    $from = setting('school_name')."\n".setting('school_address');
    $months = collect(range(1, 12))->mapWithKeys(fn ($m) => [now()->month($m)->format('F') => now()->month($m)->format('F')]);
@endphp

<div x-data="invoiceForm(@js($initial))" class="space-y-6">
    <x-card title="Invoice details">
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <x-form.select name="customer_id" label="Customer" :options="$users->pluck('name', 'id')" :value="$invoice?->customer_id" placeholder="Select customer" required wrapper="lg:col-span-2" />
            <x-form.input name="po_number" label="PO number" :value="$invoice?->po_number" required />
            <x-form.input name="invoice_date" type="date" label="Invoice date" :value="$invoice?->invoice_date ?? now()" />
            <x-form.input name="due_date" type="date" after="invoice_date" label="Due date" :value="$invoice?->due_date ?? now()->addDays((int) setting('invoice_due_days', 14))" required />
            <x-form.textarea name="invoice_from" label="Invoice from" :value="$invoice?->invoice_from ?? trim($from)" rows="3" wrapper="lg:col-span-2" />
            <x-form.textarea name="invoice_to" label="Invoice to" :value="$invoice?->invoice_to" rows="3" wrapper="lg:col-span-1" placeholder="Customer address" />
        </div>

        <div class="mt-5 border-t border-slate-200 pt-5">
            <label class="flex items-center gap-2 text-sm font-medium text-slate-700">
                <input type="checkbox" name="is_recurring" value="1" x-model="isRecurring" class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                Recurring invoice
            </label>
            <div x-show="isRecurring" x-cloak class="mt-4 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <x-form.select name="recurring_by_month" label="Starting month" :options="$months" :value="$invoice?->recurring_by_month" placeholder="Select month" />
                <x-form.input name="recurring_month" type="number" min="1" max="60" label="Number of months" :value="$invoice?->recurring_month" />
            </div>
        </div>
    </x-card>

    <x-card title="Items" :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold tracking-wide text-slate-500 uppercase">
                    <tr>
                        <th class="px-4 py-3">Item</th>
                        <th class="px-4 py-3">Category</th>
                        <th class="w-24 px-4 py-3">Qty</th>
                        <th class="w-32 px-4 py-3">Price</th>
                        <th class="w-28 px-4 py-3 whitespace-nowrap">Discount %</th>
                        <th class="w-32 px-4 py-3 text-right">Amount</th>
                        <th class="w-24 px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <template x-for="(row, index) in items" :key="index">
                        <tr>
                            <td class="px-4 py-2"><input type="text" :name="`items[${index}][item]`" x-model="row.item" required class="block w-full min-w-40 rounded-lg border-slate-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"></td>
                            <td class="px-4 py-2"><input type="text" :name="`items[${index}][category]`" x-model="row.category" required class="block w-full min-w-32 rounded-lg border-slate-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"></td>
                            <td class="px-4 py-2"><input type="number" step="0.01" min="0" :name="`items[${index}][quantity]`" x-model="row.quantity" required class="block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"></td>
                            <td class="px-4 py-2"><input type="number" step="0.01" min="0" :name="`items[${index}][price]`" x-model="row.price" required class="block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"></td>
                            <td class="px-4 py-2"><input type="number" step="0.01" min="0" max="100" :name="`items[${index}][discount]`" x-model="row.discount" class="block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"></td>
                            <td class="px-4 py-2 text-right font-medium text-slate-900" x-text="money(lineAmount(row))"></td>
                            <td class="px-4 py-2 text-right whitespace-nowrap">
                                <x-icon-link icon="copy" label="Duplicate row" @click="copyItem(index)" />
                                <x-icon-link icon="trash" label="Remove row" tone="danger" @click="removeItem(index)" ::disabled="items.length === 1" />
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        @if ($errors->has('items') || $errors->has('items.*'))
            <x-alert type="error" class="mx-4 mt-3">Every item needs a name, category, quantity and price.</x-alert>
        @endif
        <div class="p-4">
            <x-button type="button" variant="secondary" size="sm" icon="plus" @click="addItem()">Add item</x-button>
        </div>
    </x-card>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-card title="Payment details">
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-form.input name="account_holder_name" label="Account holder" :value="$invoice?->account_holder_name ?? setting('bank_account_holder')" />
                    <x-form.input name="bank_name" label="Bank name" :value="$invoice?->bank_name ?? setting('bank_name')" />
                    <x-form.input name="ifsc_code" label="IFSC / SWIFT code" :value="$invoice?->ifsc_code ?? setting('bank_ifsc_code')" />
                    <x-form.input name="account_number" label="Account number" :value="$invoice?->account_number ?? setting('bank_account_number')" />
                    <x-form.textarea name="terms_and_conditions" label="Terms and conditions" :value="$invoice?->terms_and_conditions ?? setting('invoice_terms')" wrapper="sm:col-span-2" />
                    <x-form.textarea name="notes" label="Notes" :value="$invoice?->notes ?? setting('invoice_notes')" wrapper="sm:col-span-2" />
                </div>
            </x-card>

            <x-card title="Signature">
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-form.input name="signatory_name" label="Signatory name" :value="$invoice?->signatory_name ?? auth()->user()->name" required />
                    <x-form.input name="signature" type="file" label="Signature image" accept="image/*" hint="Optional, up to 2 MB." />
                    @if ($invoice?->signature_path)
                        <img src="{{ asset($invoice->signature_path) }}" alt="Current signature" class="h-16 w-auto rounded border border-slate-200 p-1">
                    @endif
                </div>
            </x-card>
        </div>

        <x-card title="Summary" class="self-start lg:sticky lg:top-20">
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">Subtotal</dt><dd class="font-medium text-slate-900" x-text="money(subtotal)"></dd></div>

                <div class="flex items-center justify-between gap-4">
                    <dt>
                        <label class="flex items-center gap-2 text-slate-500">
                            <input type="checkbox" name="enable_tax" value="1" x-model="enableTax" class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                            <span>{{ setting('tax_name', 'Tax') }} (<span x-text="taxRate"></span>%)</span>
                        </label>
                    </dt>
                    <dd class="font-medium text-slate-900" x-text="money(taxAmount)"></dd>
                </div>

                <div class="border-t border-slate-200 pt-3">
                    <dt class="mb-2 text-slate-500">Additional charges</dt>
                    <template x-for="(charge, index) in charges" :key="index">
                        <div class="mb-2 flex items-center gap-2">
                            <input type="number" step="0.01" min="0" name="charges[]" x-model="charges[index]" class="block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500" placeholder="Amount">
                            <x-icon-link icon="trash" label="Remove charge" tone="danger" @click="charges.splice(index, 1)" />
                        </div>
                    </template>
                    <x-button type="button" variant="soft" size="sm" icon="plus" @click="charges.push(0)">Add charge</x-button>
                </div>

                <div class="border-t border-slate-200 pt-3">
                    <dt class="mb-2 text-slate-500">Discounts (% of subtotal)</dt>
                    <template x-for="(discount, index) in discounts" :key="index">
                        <div class="mb-2 flex items-center gap-2">
                            <input type="number" step="0.01" min="0" max="100" name="discounts[]" x-model="discounts[index]" class="block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500" placeholder="%">
                            <x-icon-link icon="trash" label="Remove discount" tone="danger" @click="discounts.splice(index, 1)" />
                        </div>
                    </template>
                    <x-button type="button" variant="soft" size="sm" icon="plus" @click="discounts.push(0)">Add discount</x-button>
                </div>

                <div class="flex items-center justify-between gap-4 border-t border-slate-200 pt-3">
                    <dt class="text-slate-500">Round off</dt>
                    <dd class="w-28"><input type="number" step="0.01" name="round_off" x-model="roundOff" class="block w-full rounded-lg border-slate-300 text-right text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"></dd>
                </div>

                <div class="flex justify-between border-t border-slate-200 pt-3 text-base">
                    <dt class="font-semibold text-slate-900">Total</dt>
                    <dd class="font-bold text-primary-700">{{ setting('currency_symbol') }}<span x-text="money(total)"></span></dd>
                </div>
            </dl>
            <p class="mt-4 text-xs text-slate-400">Totals are recalculated by the server when you save.</p>
        </x-card>
    </div>
</div>
