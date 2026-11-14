<x-layouts.app title="Attendance Report">
    <x-page-header title="Attendance Report" :breadcrumbs="['Attendance' => route('attendance.index', ['section_id' => $section?->id]), 'Report' => null]" />

    <x-card class="mb-6">
        <form method="GET" class="grid gap-4 sm:grid-cols-[1fr_11rem_11rem_auto] sm:items-end">
            <x-form.select name="section_id" label="Section" :options="$sectionOptions" :value="$section?->id" placeholder="Select a section" required />
            <x-form.input name="from" type="date" label="From" :value="$from" />
            <x-form.input name="to" type="date" after="from" label="To" :value="$to" />
            <x-button>Show</x-button>
        </form>
    </x-card>

    @if ($section)
        <x-table>
            <x-slot:head>
                <tr>
                    <th>Student</th>
                    @foreach ($statuses as $status)<th class="text-center">{{ $status->label() }}</th>@endforeach
                    <th class="text-center">Days recorded</th>
                    <th class="text-right">Attendance rate</th>
                </tr>
            </x-slot:head>
            @forelse ($students as $student)
                @php
                    $counts = $student->attendances->countBy(fn ($a) => $a->status->value);
                    $recorded = $student->attendances->count();
                    $rate = $recorded ? round($student->attendances->filter(fn ($a) => $a->status->isAttending())->count() / $recorded * 100, 1) : null;
                @endphp
                <tr>
                    <td class="font-medium text-slate-900">{{ $student->full_name }}</td>
                    @foreach ($statuses as $status)
                        <td class="text-center">{{ $counts[$status->value] ?? 0 }}</td>
                    @endforeach
                    <td class="text-center">{{ $recorded }}</td>
                    <td class="text-right">
                        @if ($rate === null)
                            <span class="text-slate-400">-</span>
                        @else
                            <x-badge :color="attendance_color($rate)">{{ $rate }}%</x-badge>
                        @endif
                    </td>
                </tr>
            @empty
                <x-empty-row :colspan="count($statuses) + 3" message="No students in this section." />
            @endforelse
        </x-table>
    @else
        <x-empty-state icon="calendar">Choose a section to see its attendance report.</x-empty-state>
    @endif
</x-layouts.app>
