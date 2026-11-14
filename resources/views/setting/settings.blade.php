@php
    $tabs = [
        'general' => ['label' => 'General', 'icon' => 'cog', 'route' => 'setting/page'],
        'invoice' => ['label' => 'Invoice', 'icon' => 'document', 'route' => 'invoice/settings/page'],
        'tax' => ['label' => 'Tax', 'icon' => 'currency', 'route' => 'invoice/settings/tax/page'],
        'bank' => ['label' => 'Bank', 'icon' => 'banknotes', 'route' => 'invoice/settings/bank/page'],
        'attendance' => ['label' => 'Attendance', 'icon' => 'check-circle', 'route' => 'setting/attendance'],
        'library' => ['label' => 'Library', 'icon' => 'library', 'route' => 'setting/library'],
    ];
@endphp

<x-layouts.app :title="$tabs[$group]['label'].' Settings'">
    <x-page-header title="Settings" :breadcrumbs="['Dashboard' => route('home'), 'Settings' => route('setting/page'), $tabs[$group]['label'] => null]" />

    <x-tabs :active="$group" :items="collect($tabs)->map(fn ($tab) => $tab + ['href' => route($tab['route'])])->all()" />

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
                        <div class="sm:col-span-2">
                            <input type="hidden" name="allow_registration" value="0">
                            <x-form.checkbox name="allow_registration" :switch="true" :checked="(bool) $settings['allow_registration']"
                                             hint="Shows a Sign up link on the login page so parents, students and teachers can create their own account. Off: only admins create accounts.">Allow sign up</x-form.checkbox>
                        </div>
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
                    @case('attendance')
                        <p class="text-sm text-slate-500 sm:col-span-2">Attendance rates are coloured on dashboards, profiles and the attendance report: green at or above Good, amber at or above Warning, red below.</p>
                        <x-form.input name="attendance_good" type="number" min="1" max="100" label="Good from (%)" :value="$settings['attendance_good']" required />
                        <x-form.input name="attendance_warning" type="number" min="0" max="99" label="Warning from (%)" :value="$settings['attendance_warning']" required hint="Must be lower than Good." />
                        @break
                    @case('library')
                        <x-form.input name="library_loan_days" type="number" min="1" max="365" label="Loan period (days)" :value="$settings['library_loan_days']" required hint="Default due date for new loans." />
                        <x-form.input name="library_fine_per_day" type="number" step="0.01" min="0" :label="'Late fine per day ('.setting('currency_symbol').')'" :value="$settings['library_fine_per_day']" required hint="Charged when a book is returned late. 0 = no fine." />
                        @break
                @endswitch
            </div>
            <x-form.actions submit="Save settings" />
        </form>
    </x-card>
</x-layouts.app>
