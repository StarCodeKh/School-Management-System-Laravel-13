<x-layouts.app :title="$payroll ? 'Edit Salary Record' : 'Add Salary Record'">
    <x-page-header :title="$payroll ? 'Edit Salary Record' : 'Add Salary Record'" :breadcrumbs="['Payroll' => route('payroll.index', ['month' => $month]), ($payroll ? 'Edit Salary Record' : 'Add Salary Record') => null]" />

    <x-card class="max-w-3xl">
        <form method="POST" action="{{ $payroll ? route('payroll.update', $payroll) : route('payroll.store') }}" class="space-y-6"
              x-data="{ basic: @js((float) old('basic_salary', $payroll?->basic_salary ?? 0)), allow: @js((float) old('allowances', $payroll?->allowances ?? 0)), deduct: @js((float) old('deductions', $payroll?->deductions ?? 0)), status: @js(old('status', $payroll?->status?->value ?? 'pending')) }">
            @csrf
            @if ($payroll) @method('PUT') @endif
            <x-form.section title="Staff and month" description="Admins, teachers and staff accounts." icon="user">
                <x-form.select name="user_id" label="Staff member" :options="$staff" :value="$payroll?->user_id" placeholder="Select staff member" required icon="user" />
                <x-form.input name="month" type="month" label="Month" :value="$month" required />
            </x-form.section>

            <x-form.section title="Salary" description="The net salary is worked out as you type." icon="banknotes">
                <x-form.input name="basic_salary" type="number" step="0.01" min="0" label="Basic salary" x-model.number="basic" required />
                <x-form.input name="allowances" type="number" step="0.01" min="0" label="Allowances" x-model.number="allow" />
                <x-form.input name="deductions" type="number" step="0.01" min="0" label="Deductions" x-model.number="deduct" />
                <x-form.field label="Net salary">
                    <p class="rounded-lg bg-slate-50 px-3 py-2 text-lg font-bold text-slate-900">{{ setting('currency_symbol') }}<span x-text="Math.max(0, basic + allow - deduct).toFixed(2)"></span></p>
                </x-form.field>
            </x-form.section>

            <x-form.section title="Payment" description="Mark it paid once the money has gone out." icon="currency">
                <x-form.select name="status" label="Status" :options="\App\Enums\PayrollStatus::options()" :value="$payroll?->status ?? 'pending'" :placeholder="false" x-model="status" required />
                <div x-show="status === 'paid'" x-cloak class="grid gap-5 sm:col-span-2 sm:grid-cols-2">
                    <x-form.input name="paid_on" type="date" label="Paid on" :value="$payroll?->paid_on ?? today()" />
                    <x-form.select name="payment_method" label="Payment method" :options="\App\Enums\PaymentMethod::options()" :value="$payroll?->payment_method" placeholder="Select method" />
                </div>
                <x-form.textarea name="notes" label="Notes" :value="$payroll?->notes" wrapper="sm:col-span-2" />
            </x-form.section>
            <x-form.actions :submit="$payroll ? 'Save changes' : 'Save record'" :cancel="route('payroll.index', ['month' => $month])" />
        </form>
    </x-card>
</x-layouts.app>
