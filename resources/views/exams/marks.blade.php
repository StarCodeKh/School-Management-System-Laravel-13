<x-layouts.app :title="'Marks - '.$exam->name">
    <x-page-header :title="'Enter marks: '.$exam->name" :breadcrumbs="['Exams' => route('exams.index'), $exam->name => route('exams.show', $exam), 'Marks' => null]" />

    @if ($subjects->isEmpty())
        <x-empty-state icon="book">This class has no subjects yet.</x-empty-state>
    @else
        <nav class="mb-6 flex gap-1 overflow-x-auto border-b border-slate-200">
            @foreach ($subjects as $item)
                <a href="{{ route('exams.marks', [$exam, 'subject_id' => $item->id]) }}" @class([
                    '-mb-px border-b-2 px-4 py-2 text-sm font-medium whitespace-nowrap',
                    'border-primary-600 text-primary-700' => $subject->is($item),
                    'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' => ! $subject->is($item),
                ])>{{ $item->subject_name }}</a>
            @endforeach
        </nav>

        <form method="POST" action="{{ route('exams.marks.save', $exam) }}">
            @csrf
            <input type="hidden" name="subject_id" value="{{ $subject->id }}">
            <x-table>
                <x-slot:head>
                    <tr><th>Student</th><th>Section</th><th>Marks (out of {{ $exam->max_marks }})</th><th>Remark</th></tr>
                </x-slot:head>
                @forelse ($students as $student)
                    @php($key = "marks.{$student->id}")
                    <tr>
                        <td class="font-medium text-slate-900">{{ $student->full_name }}</td>
                        <td>{{ $student->section?->name }}</td>
                        <td class="w-44">
                            <input type="number" step="0.01" min="0" max="{{ $exam->max_marks }}" name="marks[{{ $student->id }}]"
                                   value="{{ old($key, isset($results[$student->id]) ? (float) $results[$student->id]->marks : '') }}"
                                   @class(['block w-full rounded-lg text-sm shadow-sm', 'border-slate-300 focus:border-primary-500 focus:ring-primary-500' => ! $errors->has($key), 'border-red-400' => $errors->has($key)])>
                            @error($key)<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </td>
                        <td class="min-w-48">
                            <input type="text" name="remarks[{{ $student->id }}]" value="{{ old("remarks.{$student->id}", $results[$student->id]->remark ?? '') }}" placeholder="Optional"
                                   class="form-control">
                        </td>
                    </tr>
                @empty
                    <x-empty-row colspan="4" message="No students in this class." />
                @endforelse
            </x-table>
            <div class="mt-4 flex items-center justify-between">
                <p class="text-xs text-slate-500">Leave a box empty to remove that student's mark.</p>
                <x-button icon="check">Save {{ $subject->subject_name }} marks</x-button>
            </div>
        </form>
    @endif
</x-layouts.app>
