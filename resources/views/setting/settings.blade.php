@php
    $tabs = [
        'general' => ['label' => 'General', 'route' => 'setting/page'],
        'invoice' => ['label' => 'Invoice', 'route' => 'invoice/settings/page'],
        'tax' => ['label' => 'Tax', 'route' => 'invoice/settings/tax/page'],
        'bank' => ['label' => 'Bank', 'route' => 'invoice/settings/bank/page'],
    ];
@endphp

<x-layouts.app :title="$tabs[$group]['label'].' Settings'">
    <x-page-header title="Settings" :breadcrumbs="['Dashboard' => route('home'), 'Settings' => route('setting/page'), $tabs[$group]['label'] => null]" />

    <nav class="mb-6 flex gap-1 overflow-x-auto border-b border-slate-200">
        @foreach ($tabs as $key => $tab)
            <a href="{{ route($tab['route']) }}" @class([
                '-mb-px border-b-2 px-4 py-2 text-sm font-medium whitespace-nowrap',
                'border-primary-600 text-primary-700' => $group === $key,
                'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' => $group !== $key,
            ])>{{ $tab['label'] }}</a>
        @endforeach
    </nav>

    <x-card class="max-w-3xl">
        <form method="POST" action="{{ route('setting/update', $group) }}" class="space-y-6">
            @csrf
            <div class="grid gap-5 sm:grid-cols-2">
                @switch($group)
                    @case('general')
                        <x-form.input name="school_name" label="School name" :value="$settings['school_name']" wrapper="sm:col-span-2" required hint="Shown in the sidebar, page titles and on invoices." />
                        <x-form.input name="school_email" type="email" label="Email" :value="$settings['school_email']" />
                        <x-form.input name="school_phone" label="Phone" :value="$settings['school_phone']" />
                        <x-form.textarea name="school_address" label="Address" :value="$settings['school_address']" wrapper="sm:col-span-2" />
                        <x-form.input name="currency_symbol" label="Currency symbol" :value="$settings['currency_symbol']" maxlength="5" required hint="Used for every amount, e.g. $ or ៛." />
                        @break
                    @case('invoice')
                        <x-form.input name="invoice_due_days" type="number" min="0" max="365" label="Default due in (days)" :value="$settings['invoice_due_days']" required hint="Pre-fills the due date on new invoices." />
                        <x-form.textarea name="invoice_terms" label="Default terms and conditions" :value="$settings['invoice_terms']" rows="4" wrapper="sm:col-span-2" />
                        <x-form.textarea name="invoice_notes" label="Default notes" :value="$settings['invoice_notes']" rows="3" wrapper="sm:col-span-2" />
                        @break
                    @case('tax')
                        <x-form.input name="tax_name" label="Tax name" :value="$settings['tax_name']" required hint="e.g. VAT" />
                        <x-form.input name="tax_rate" type="number" step="0.01" min="0" max="100" label="Tax rate (%)" :value="$settings['tax_rate']" required hint="Applied to invoices that have tax enabled." />
                        @break
                    @case('bank')
                        <p class="text-sm text-slate-500 sm:col-span-2">These bank details pre-fill new invoices.</p>
                        <x-form.input name="bank_account_holder" label="Account holder" :value="$settings['bank_account_holder']" />
                        <x-form.input name="bank_name" label="Bank name" :value="$settings['bank_name']" />
                        <x-form.input name="bank_ifsc_code" label="IFSC / SWIFT code" :value="$settings['bank_ifsc_code']" />
                        <x-form.input name="bank_account_number" label="Account number" :value="$settings['bank_account_number']" />
                        @break
                @endswitch
            </div>
            <x-form.actions submit="Save settings" />
        </form>
    </x-card>
</x-layouts.app>
