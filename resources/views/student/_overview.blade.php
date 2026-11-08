{{-- Fees, attendance and exam results of one student. Needs $student, $fees, $attendanceRate, $recentAttendance, $examRows. --}}
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
            <p class="text-3xl font-bold {{ $attendanceRate >= 90 ? 'text-emerald-600' : ($attendanceRate >= 75 ? 'text-amber-600' : 'text-red-600') }}">{{ $attendanceRate }}%</p>
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
