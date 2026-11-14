{{-- Fees, attendance, exam results and school services of one student (data from StudentController::overview()). --}}
<div class="grid gap-6 xl:grid-cols-3">
    <x-card title="Fees">
        <div class="grid grid-cols-3 gap-2 text-center">
            <div><p class="text-xs text-slate-500 uppercase">Due</p><p class="font-bold text-slate-900">{{ money($fees['due']) }}</p></div>
            <div><p class="text-xs text-slate-500 uppercase">Paid</p><p class="font-bold text-emerald-600">{{ money($fees['paid']) }}</p></div>
            <div><p class="text-xs text-slate-500 uppercase">Balance</p><p class="font-bold {{ $fees['balance'] > 0 ? 'text-red-600' : 'text-slate-900' }}">{{ money($fees['balance']) }}</p></div>
        </div>
        @if ($fees['by_type']->isNotEmpty())
            <ul class="mt-4 space-y-2 border-t border-slate-100 pt-4 text-sm">
                @foreach ($fees['by_type'] as $line)
                    <li class="flex justify-between gap-3">
                        <span class="text-slate-600">{{ $line['fee_type']?->name }}@if ($line['due_date']) <span class="text-xs text-slate-400">&middot; due {{ $line['due_date']->format('d M') }}</span>@endif</span>
                        <span @class(['font-medium', 'text-emerald-600' => $line['paid'] >= $line['due'], 'text-slate-900' => $line['paid'] < $line['due']])>{{ money(min($line['paid'], $line['due'])) }} / {{ money($line['due']) }}</span>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="mt-4 text-sm text-slate-500">No fee structure set for this class.</p>
        @endif
    </x-card>

    <x-card title="Attendance">
        @if ($attendanceRate === null)
            <p class="text-sm text-slate-500">No attendance recorded yet.</p>
        @else
            <p class="text-3xl font-bold {{ ['green' => 'text-emerald-600', 'amber' => 'text-amber-600', 'red' => 'text-red-600'][attendance_color($attendanceRate)] }}">{{ $attendanceRate }}%</p>
            <p class="text-sm text-slate-500">attendance rate</p>
            <ul class="mt-4 flex flex-wrap gap-1.5">
                @foreach ($recentAttendance as $record)
                    <li title="{{ $record->date->format('D d M') }}: {{ $record->status->label() }}{{ $record->remark ? ' - '.$record->remark : '' }}">
                        <x-badge :color="$record->status->color()">{{ $record->date->format('d M') }}</x-badge>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-card>

    <x-card title="Exam results" :padding="false">
        <ul class="divide-y divide-slate-100">
            @forelse ($examRows as $item)
                <li class="flex items-center justify-between gap-3 px-5 py-3 text-sm">
                    <div>
                        <p class="font-medium text-slate-900">{{ $item['exam']->name }}</p>
                        <p class="text-xs text-slate-500">{{ $item['row'] ? $item['row']['percentage'].'% · rank '.$item['row']['rank'] : 'No marks' }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        @if ($item['row'])<x-badge :color="$item['row']['grade']->color()">{{ $item['row']['grade']->value }}</x-badge>@endif
                        <x-icon-link :href="route('exams.report-card', [$item['exam'], $student])" icon="document" label="Report card" tone="primary" />
                    </div>
                </li>
            @empty
                <li class="px-5 py-8 text-center text-sm text-slate-500">No exam results yet.</li>
            @endforelse
        </ul>
    </x-card>
</div>

<div class="mt-6 grid gap-6 xl:grid-cols-3">
    <x-card title="Library books" :padding="false">
        <ul class="divide-y divide-slate-100">
            @forelse ($activeLoans as $loan)
                <li class="flex items-center justify-between gap-3 px-5 py-3 text-sm">
                    <span class="font-medium text-slate-900">{{ $loan->book?->title }}</span>
                    @if ($loan->isOverdue())
                        <x-badge color="red">Overdue since {{ $loan->due_on->format('d M') }}</x-badge>
                    @else
                        <span class="text-xs text-slate-500">Due {{ $loan->due_on->format('d M Y') }}</span>
                    @endif
                </li>
            @empty
                <li class="px-5 py-8 text-center text-sm text-slate-500">No books on loan.</li>
            @endforelse
        </ul>
    </x-card>

    <x-card title="Transport">
        @if ($transport)
            <p class="font-semibold text-slate-900">{{ $transport->route?->name }}</p>
            <p class="text-sm text-slate-500">Pickup: {{ $transport->pickup_stop ?: 'not set' }}</p>
            @if ($transport->route?->vehicle)
                <p class="mt-2 text-sm text-slate-600">{{ $transport->route->vehicle->registration_number }} &middot; driver {{ $transport->route->vehicle->driver_name ?: '-' }} {{ $transport->route->vehicle->driver_phone }}</p>
            @endif
        @else
            <p class="text-sm text-slate-500">Not using school transport.</p>
        @endif
    </x-card>

    <x-card title="Hostel">
        @if ($hostel)
            <p class="font-semibold text-slate-900">{{ $hostel->room?->label() }}</p>
            <p class="text-sm text-slate-500">Since {{ $hostel->allocated_on->format('d M Y') }}</p>
        @else
            <p class="text-sm text-slate-500">Not staying in the hostel.</p>
        @endif
    </x-card>
</div>
