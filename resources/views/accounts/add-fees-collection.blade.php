<x-layouts.app title="Add Fee Payment">
    <x-page-header title="Add Fee Payment" :breadcrumbs="['Fees Collection' => route('account/fees/collections/page'), 'Add Payment' => null]" />

    <x-card class="max-w-3xl">
        <form method="POST" action="{{ route('fees/collection/save') }}" class="space-y-6"
              x-data="{ codes: @js($students->pluck('admission_id', 'id')), balances: @js($balances), studentId: @js((string) old('student_id', '')) }">
            @csrf
            <x-form.section title="Payment" description="Pick the student first to see what they still owe." icon="banknotes">
                <x-form.select name="student_id" label="Student" required x-model="studentId" placeholder="Select student"
                               :options="$students->mapWithKeys(fn ($student) => [$student->id => $student->full_name.' (Class '.$student->schoolClass?->name.' - '.$student->section?->name.')'])" />
                <x-form.field label="Admission ID">
                    <input type="text" readonly :value="codes[studentId] ?? ''" class="form-control bg-slate-50 text-slate-500">
                    <p x-show="studentId" x-cloak class="mt-1.5 text-sm text-slate-600">Outstanding balance: <span class="font-semibold text-slate-900" x-text="balances[studentId]"></span></p>
                </x-form.field>
                <x-form.select name="fee_type_id" label="Fee type" :options="$feeTypes->pluck('name', 'id')" placeholder="Select fee type" required />
                <x-form.input name="amount" type="number" step="0.01" min="0" label="Amount ({{ setting('currency_symbol') }})" required />
                <x-form.input name="paid_date" type="date" label="Paid date" :value="now()" required />
            </x-form.section>
            <x-form.actions submit="Save payment" :cancel="route('account/fees/collections/page')" />
        </form>
    </x-card>
</x-layouts.app>
