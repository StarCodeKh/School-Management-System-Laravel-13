<x-layouts.app :title="'Report card - '.$student->full_name">
    <x-page-header title="Report Card" :breadcrumbs="['Exams' => auth()->user()->can('teach') ? route('exams.show', $exam) : null, $student->full_name => null]">
        <x-slot:actions>
            <x-button variant="secondary" icon="printer" type="button" onclick="window.print()">Print</x-button>
        </x-slot:actions>
    </x-page-header>

    <article class="mx-auto max-w-3xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm sm:p-10 print:border-0 print:shadow-none">
        <header class="flex items-center justify-between border-b border-slate-200 pb-6">
            <div class="flex items-center gap-3">
                <span class="grid size-10 place-items-center rounded-lg bg-primary-600 text-white"><x-icon name="academic-cap" /></span>
                <div>
                    <p class="text-lg font-bold text-slate-900">{{ setting('school_name') }}</p>
                    <p class="text-sm text-slate-500">{{ $exam->name }}@if ($exam->term) &middot; {{ $exam->term }}@endif</p>
                </div>
            </div>
            <x-avatar :src="$student->photo_url" :alt="$student->full_name" size="size-14" />
        </header>

        <dl class="grid grid-cols-2 gap-4 border-b border-slate-200 py-6 text-sm sm:grid-cols-4">
            <div><dt class="text-xs text-slate-500 uppercase">Student</dt><dd class="font-semibold text-slate-900">{{ $student->full_name }}</dd></div>
            <div><dt class="text-xs text-slate-500 uppercase">Admission ID</dt><dd class="text-slate-900">{{ $student->admission_id }}</dd></div>
            <div><dt class="text-xs text-slate-500 uppercase">Class</dt><dd class="text-slate-900">{{ $student->schoolClass?->name }} - {{ $student->section?->name }}</dd></div>
            <div><dt class="text-xs text-slate-500 uppercase">Roll</dt><dd class="text-slate-900">{{ $student->roll }}</dd></div>
        </dl>

        <table class="mt-6 min-w-full text-sm">
            <thead class="border-b border-slate-200 text-left text-xs font-semibold tracking-wide text-slate-500 uppercase">
                <tr><th class="py-2">Subject</th><th class="py-2 text-right">Marks</th><th class="py-2 text-right">Out of</th><th class="py-2 text-center">Grade</th><th class="py-2">Remark</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($subjects as $subject)
                    @php($result = $results[$subject->id] ?? null)
                    <tr>
                        <td class="py-2.5 font-medium text-slate-900">{{ $subject->subject_name }}</td>
                        <td class="py-2.5 text-right">{{ $result ? (float) $result->marks : '-' }}</td>
                        <td class="py-2.5 text-right text-slate-500">{{ $exam->max_marks }}</td>
                        <td class="py-2.5 text-center">@if ($result)<x-badge :color="$result->grade()->color()">{{ $result->grade()->value }}</x-badge>@else - @endif</td>
                        <td class="py-2.5 text-slate-500">{{ $result?->remark }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if ($row)
            <div class="mt-6 grid grid-cols-2 gap-4 rounded-lg bg-slate-50 p-4 text-center text-sm sm:grid-cols-4">
                <div><p class="text-xs text-slate-500 uppercase">Total</p><p class="text-lg font-bold text-slate-900">{{ (float) $row['total'] }} / {{ $subjects->count() * $exam->max_marks }}</p></div>
                <div><p class="text-xs text-slate-500 uppercase">Percentage</p><p class="text-lg font-bold text-slate-900">{{ $row['percentage'] }}%</p></div>
                <div><p class="text-xs text-slate-500 uppercase">Grade</p><p class="text-lg font-bold text-slate-900">{{ $row['grade']->value }}</p></div>
                <div><p class="text-xs text-slate-500 uppercase">Rank</p><p class="text-lg font-bold text-slate-900">{{ $row['rank'] }} of {{ $classSize }}</p></div>
            </div>
            <p class="mt-4 text-center text-sm font-semibold {{ $row['grade']->passed() ? 'text-emerald-600' : 'text-red-600' }}">{{ $row['grade']->passed() ? 'Passed' : 'Not passed' }}</p>
        @endif
    </article>
</x-layouts.app>
