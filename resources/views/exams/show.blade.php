<x-layouts.app :title="$exam->name">
    <x-page-header :title="$exam->name" :breadcrumbs="['Exams' => route('exams.index'), $exam->name => null]">
        <x-slot:actions>
            <x-button variant="secondary" icon="printer" type="button" onclick="window.print()">Print</x-button>
            <x-button :href="route('exams.marks', $exam)" icon="pencil">Enter marks</x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat-card label="Class" :value="$exam->schoolClass?->name" icon="squares" />
        <x-stat-card label="Subjects" :value="$subjects->count()" icon="book" color="sky" />
        <x-stat-card label="Students" :value="$sheet->count()" icon="academic-cap" color="amber" />
        <x-stat-card label="Pass rate" :value="$sheet->isEmpty() ? '-' : round($sheet->filter(fn ($row) => $row['grade']->passed())->count() / $sheet->count() * 100).'%'" icon="check-circle" color="green" />
    </div>

    @if ($subjects->isEmpty())
        <x-empty-state icon="clipboard">
            Class {{ $exam->schoolClass?->name }} has no subjects yet. <a href="{{ route('subject/add/page') }}" class="text-primary-600 hover:underline">Add subjects</a> first.
        </x-empty-state>
    @else
        <x-table>
            <x-slot:head>
                <tr>
                    <th>Rank</th><th>Student</th>
                    @foreach ($subjects as $subject)<th class="text-center">{{ $subject->subject_name }}</th>@endforeach
                    <th class="text-center">Total</th><th class="text-center">%</th><th class="text-center">Grade</th><th class="text-right print:hidden">Report</th>
                </tr>
            </x-slot:head>
            @forelse ($sheet as $row)
                <tr class="hover:bg-slate-50">
                    <td class="font-semibold text-slate-900">{{ $row['rank'] }}</td>
                    <td>
                        <p class="font-medium text-slate-900">{{ $row['student']->full_name }}</p>
                        <p class="text-xs text-slate-500">{{ $row['student']->section?->name ? 'Section '.$row['student']->section->name : '' }}</p>
                    </td>
                    @foreach ($subjects as $subject)
                        @php($mark = $row['marks'][$subject->id])
                        <td @class(['text-center', 'text-red-600' => $mark !== null && $mark / $exam->max_marks * 100 < 50])>{{ $mark === null ? '-' : rtrim(rtrim(number_format($mark, 2), '0'), '.') }}</td>
                    @endforeach
                    <td class="text-center font-medium">{{ rtrim(rtrim(number_format($row['total'], 2), '0'), '.') }}</td>
                    <td class="text-center">{{ $row['percentage'] }}%</td>
                    <td class="text-center"><x-badge :color="$row['grade']->color()">{{ $row['grade']->value }}</x-badge></td>
                    <td class="text-right print:hidden">
                        <x-icon-link :href="route('exams.report-card', [$exam, $row['student']])" icon="document" label="Report card" tone="primary" />
                    </td>
                </tr>
            @empty
                <x-empty-row :colspan="$subjects->count() + 6" message="No students in this class." />
            @endforelse
        </x-table>
        <p class="mt-3 text-xs text-slate-500">Grades: A+ &ge; 90%, A &ge; 80%, B &ge; 70%, C &ge; 60%, D &ge; 50%, F below 50%. Missing marks count as 0.</p>
    @endif
</x-layouts.app>
