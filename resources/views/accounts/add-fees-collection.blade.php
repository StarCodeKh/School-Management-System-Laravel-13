<x-layouts.app title="Add Fee Payment">
    <x-page-header title="Add Fee Payment" :breadcrumbs="['Fees Collection' => route('account/fees/collections/page'), 'Add Payment' => null]" />

    <x-card class="max-w-4xl">
        <form method="POST" action="{{ route('fees/collection/save') }}" class="space-y-6"
              x-data="{ codes: @js($students->pluck('admission_id', 'id')), studentId: @js((string) old('student_id', '')) }">
            @csrf
            <div class="grid gap-5 sm:grid-cols-2">
                <x-form.select name="student_id" label="Student" required x-model="studentId" placeholder="Select student"
                               :options="$students->mapWithKeys(fn ($student) => [$student->id => $student->full_name.' (Class '.$student->schoolClass?->name.' - '.$student->section?->name.')'])" />
                <x-form.field label="Admission ID">
                    <input type="text" readonly :value="codes[studentId] ?? ''" class="block w-full rounded-lg border-slate-300 bg-slate-50 text-sm text-slate-500 shadow-sm">
                </x-form.field>
                <x-form.select name="fee_type_id" label="Fee type" :options="$feeTypes->pluck('name', 'id')" placeholder="Select fee type" required />
                <x-form.input name="amount" type="number" step="0.01" min="0" label="Amount ({{ setting('currency_symbol') }})" required />
                <x-form.input name="paid_date" type="date" label="Paid date" :value="now()" required />
            </div>
            <x-form.actions submit="Save payment" :cancel="route('account/fees/collections/page')" />
        </form>
    </x-card>
</x-layouts.app>
