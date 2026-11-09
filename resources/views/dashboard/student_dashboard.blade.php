<x-layouts.app title="Student Dashboard">
    <x-page-header :title="'Welcome, '.auth()->user()->name.'!'" :breadcrumbs="['Home' => route('home'), 'Student Dashboard' => null]" />

    @unless ($student)
        <x-alert type="warning" title="Account not linked yet" class="mb-6">Your account is not linked to a student record yet. An administrator can link it from the student's edit page (<strong>Login account</strong>).</x-alert>
    @endunless

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat-card label="Class" :value="$student ? $student->schoolClass?->name.' - '.$student->section?->name : '-'" icon="squares" />
        <x-stat-card label="Subjects" :value="$subjects->count()" icon="book" color="sky" />
        <x-stat-card label="Fee balance" :value="money($fees['balance'] ?? 0)" icon="banknotes" :color="($fees['balance'] ?? 0) > 0 ? 'red' : 'green'" />
        <x-stat-card label="Outstanding invoices" :value="money($outstanding)" icon="document" :color="$outstanding > 0 ? 'red' : 'green'" />
    </div>

    @if ($student)
        <div class="mt-6">@include('student._overview')</div>

        <x-card :title="'Today\'s timetable'" :padding="false" class="mt-6">
            <ul class="divide-y divide-slate-100">
                @forelse ($todayTimetable as $entry)
                    <li class="flex items-center justify-between gap-3 px-5 py-2.5 text-sm">
                        <span class="font-medium text-slate-900">{{ $entry->subject?->subject_name }}</span>
                        <span class="text-slate-500">{{ $entry->timeRange() }} &middot; {{ $entry->teacher?->full_name }}</span>
                    </li>
                @empty
                    <li class="px-5 py-8 text-center text-sm text-slate-500">No classes today.</li>
                @endforelse
            </ul>
        </x-card>
    @endif

    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        <x-card title="My details">
            @if ($student)
                <div class="flex items-center gap-3">
                    <x-avatar :src="$student->photo_url" :alt="$student->full_name" size="size-14" />
                    <div>
                        <p class="font-semibold text-slate-900">{{ $student->full_name }}</p>
                        <p class="text-sm text-slate-500">{{ $student->admission_id }}</p>
                    </div>
                </div>
                <dl class="mt-5 space-y-3 text-sm">
                    @foreach ([
                        'Roll number' => $student->roll,
                        'Classmates in section' => $classmates,
                        'Date of birth' => $student->date_of_birth?->format('d M Y'),
                        'Blood group' => $student->blood_group?->label(),
                        'Phone' => $student->phone_number,
                    ] as $label => $value)
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500">{{ $label }}</dt>
                            <dd class="text-right font-medium text-slate-900">{{ $value ?? '-' }}</dd>
                        </div>
                    @endforeach
                </dl>
            @else
                <p class="text-sm text-slate-500">No student record linked to this account.</p>
            @endif
        </x-card>

        <x-card title="My subjects" :padding="false">
            <ul class="divide-y divide-slate-100">
                @forelse ($subjects as $subject)
                    <li class="flex items-center justify-between px-5 py-3 text-sm">
                        <span class="font-medium text-slate-900">{{ $subject->subject_name }}</span>
                        <span class="text-xs text-slate-400">{{ $subject->code }}</span>
                    </li>
                @empty
                    <li class="px-5 py-10 text-center text-sm text-slate-500">No subjects for your class yet.</li>
                @endforelse
            </ul>
        </x-card>

        <x-card title="Fee payments" :padding="false">
            <ul class="divide-y divide-slate-100">
                @forelse ($student?->feeCollections?->sortByDesc('paid_date') ?? [] as $payment)
                    <li class="flex items-center justify-between px-5 py-3 text-sm">
                        <div>
                            <p class="font-medium text-slate-900">{{ $payment->feeType?->name }}</p>
                            <p class="text-xs text-slate-500">{{ $payment->paid_date->format('d M Y') }}</p>
                        </div>
                        <span class="font-semibold text-emerald-600">{{ money($payment->amount) }}</span>
                    </li>
                @empty
                    <li class="px-5 py-10 text-center text-sm text-slate-500">No payments yet.</li>
                @endforelse
            </ul>
        </x-card>
    </div>

    <x-card title="My invoices" :padding="false" class="mt-6">
        <x-table class="rounded-none border-0 shadow-none">
            <x-slot:head>
                <tr><th>Invoice</th><th>Due date</th><th>Amount</th><th>Status</th></tr>
            </x-slot:head>
            @forelse ($invoices as $invoice)
                <tr>
                    <td class="font-medium text-slate-900">{{ $invoice->invoice_number }}</td>
                    <td>{{ $invoice->due_date->format('d M Y') }}</td>
                    <td>{{ money($invoice->total_amount) }}</td>
                    <td><x-invoice-status :invoice="$invoice" /></td>
                </tr>
            @empty
                <x-empty-row colspan="4" message="No invoices." />
            @endforelse
        </x-table>
    </x-card>
    <div class="mt-6"><x-noticeboard /></div>
</x-layouts.app>
