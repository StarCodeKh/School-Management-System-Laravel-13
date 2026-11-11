<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/** Exams per class, marks entry per subject, result sheets and report cards. */
class ExamController extends Controller
{
    public function index(Request $request)
    {
        $classId = $request->integer('class') ?: null;

        $exams = Exam::with('schoolClass')->withCount('results')
            ->when($classId, fn ($q) => $q->where('school_class_id', $classId))
            ->sortable([
                'name' => 'name',
                'class' => fn ($q, $d) => $q->orderBy(SchoolClass::select('sort_order')->whereColumn('school_classes.id', 'exams.school_class_id'), $d)
                    ->orderBy(SchoolClass::select('name')->whereColumn('school_classes.id', 'exams.school_class_id'), $d),
                'term' => 'term',
                'dates' => 'start_date',
                'max_marks' => 'max_marks',
                'marks' => 'results_count',
            ], 'dates', 'desc')
            ->paginateFromRequest(15);

        $classes = SchoolClass::orderBy('sort_order')->orderBy('name')->get();

        return view('exams.index', compact('exams', 'classes', 'classId'));
    }

    public function create()
    {
        return view('exams.form', ['exam' => null, 'classes' => $this->classOptions()]);
    }

    public function store(Request $request)
    {
        $exam = Exam::create($request->validate($this->rules($request)));

        return redirect()->route('exams.show', $exam)->with('success', 'Exam created. You can now enter marks.');
    }

    /** result sheet for the whole class */
    public function show(Exam $exam)
    {
        $exam->load('schoolClass');
        $subjects = $exam->subjects()->get();
        $sheet = $exam->resultSheet();

        return view('exams.show', compact('exam', 'subjects', 'sheet'));
    }

    public function edit(Exam $exam)
    {
        return view('exams.form', ['exam' => $exam, 'classes' => $this->classOptions()]);
    }

    public function update(Request $request, Exam $exam)
    {
        $exam->update($request->validate($this->rules($request, $exam)));

        return redirect()->route('exams.show', $exam)->with('success', 'Exam updated.');
    }

    public function destroy(Exam $exam)
    {
        $exam->delete(); // results are removed by the foreign key cascade

        return redirect()->route('exams.index')->with('success', 'Exam deleted.');
    }

    /** marks entry form for one subject */
    public function marks(Request $request, Exam $exam)
    {
        $subjects = $exam->subjects()->get();
        $subject = $subjects->firstWhere('id', $request->integer('subject_id')) ?? $subjects->first();

        $students = $exam->students()->with('section')->orderBy('first_name')->get();
        $results = $subject
            ? $exam->results()->where('subject_id', $subject->id)->get()->keyBy('student_id')
            : collect();

        // marks entered per subject, for the progress shown on each subject tab
        $entered = $exam->results()->whereNotNull('marks')->whereIn('student_id', $students->pluck('id'))
            ->selectRaw('subject_id, count(*) as total')->groupBy('subject_id')->pluck('total', 'subject_id');

        return view('exams.marks', compact('exam', 'subjects', 'subject', 'students', 'results', 'entered'));
    }

    public function saveMarks(Request $request, Exam $exam)
    {
        $validated = $request->validate([
            'subject_id' => ['required', Rule::exists('subjects', 'id')->where('school_class_id', $exam->school_class_id)],
            'marks' => 'required|array',
            'marks.*' => 'nullable|numeric|min:0|max:'.$exam->max_marks,
            'remarks' => 'nullable|array',
            'remarks.*' => 'nullable|string|max:255',
        ]);

        $studentIds = $exam->students()->pluck('id')->all();
        $saved = 0;

        DB::transaction(function () use ($validated, $exam, $studentIds, &$saved) {
            foreach ($validated['marks'] as $studentId => $mark) {
                if (! in_array((int) $studentId, $studentIds, true)) {
                    continue;
                }

                $key = ['exam_id' => $exam->id, 'student_id' => $studentId, 'subject_id' => $validated['subject_id']];

                if ($mark === null || $mark === '') {
                    ExamResult::where($key)->delete(); // clearing a box removes the mark

                    continue;
                }

                ExamResult::updateOrCreate($key, ['marks' => $mark, 'remark' => $validated['remarks'][$studentId] ?? null]);
                $saved++;
            }
        });

        return redirect()->route('exams.marks', [$exam, 'subject_id' => $validated['subject_id']])
            ->with('success', "Saved marks for {$saved} students.");
    }

    /** printable report card for one student */
    public function reportCard(Request $request, Exam $exam, Student $student)
    {
        abort_unless($student->school_class_id === $exam->school_class_id, 404);
        $this->authorizeStudentAccess($request, $student);

        $exam->load('schoolClass');
        $row = $exam->resultSheet()->firstWhere('student.id', $student->id);
        $subjects = $exam->subjects()->get();
        $results = $exam->results()->with('exam')->where('student_id', $student->id)->get()->keyBy('subject_id');
        $classSize = $exam->students()->count();

        return view('exams.report-card', compact('exam', 'student', 'row', 'subjects', 'results', 'classSize'));
    }

    /** staff can see every report card; students and parents only their own */
    private function authorizeStudentAccess(Request $request, Student $student): void
    {
        $user = $request->user();

        abort_unless($user->can('teach') || $student->user_id === $user->id || $user->canViewStudent($student), 403);
    }

    private function classOptions(): array
    {
        return SchoolClass::orderBy('sort_order')->orderBy('name')->pluck('name', 'id')->map(fn ($name) => 'Class '.$name)->all();
    }

    private function rules(Request $request, ?Exam $ignore = null): array
    {
        return [
            'school_class_id' => 'required|exists:school_classes,id',
            'name' => ['required', 'string', 'max:255', Rule::unique('exams')->where('school_class_id', $request->input('school_class_id'))->ignore($ignore?->id)],
            'term' => 'nullable|string|max:50',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'max_marks' => 'required|integer|min:1|max:1000',
        ];
    }
}
