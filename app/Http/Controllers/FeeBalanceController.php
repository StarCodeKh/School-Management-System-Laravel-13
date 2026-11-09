<?php

namespace App\Http\Controllers;

use App\Models\FeeCollection;
use App\Models\FeeStructure;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\Request;

/** Outstanding fees per student: class fee structure minus payments made. */
class FeeBalanceController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search'));
        $classId = $request->integer('class') ?: null;

        $students = Student::with(['schoolClass', 'section'])
            ->when($search, fn ($q) => $q->where(fn ($q) => $q
                ->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('admission_id', 'like', "%{$search}%")))
            ->when($classId, fn ($q) => $q->where('school_class_id', $classId))
            ->sortable([
                'student' => ['first_name', 'last_name'],
                'class' => fn ($q, $d) => $q->orderBy(SchoolClass::select('sort_order')->whereColumn('school_classes.id', 'students.school_class_id'), $d)
                    ->orderBy(SchoolClass::select('name')->whereColumn('school_classes.id', 'students.school_class_id'), $d),
            ], 'student')
            ->paginateFromRequest(20);

        // two grouped queries for the whole page instead of one per student
        $dueByClass = FeeStructure::selectRaw('school_class_id, SUM(amount) as total')->groupBy('school_class_id')->pluck('total', 'school_class_id');
        $paidByStudent = FeeCollection::whereIn('student_id', $students->pluck('id'))
            ->selectRaw('student_id, SUM(amount) as total')->groupBy('student_id')->pluck('total', 'student_id');

        $balances = $students->getCollection()->mapWithKeys(function (Student $student) use ($dueByClass, $paidByStudent) {
            $due = (float) ($dueByClass[$student->school_class_id] ?? 0);
            $paid = (float) ($paidByStudent[$student->id] ?? 0);

            return [$student->id => ['due' => $due, 'paid' => $paid, 'balance' => max(0, $due - $paid)]];
        });

        $totals = [
            'due' => Student::query()->when($classId, fn ($q) => $q->where('school_class_id', $classId))->get(['school_class_id'])
                ->sum(fn ($student) => (float) ($dueByClass[$student->school_class_id] ?? 0)),
            'paid' => (float) FeeCollection::when($classId, fn ($q) => $q->whereHas('student', fn ($s) => $s->where('school_class_id', $classId)))->sum('amount'),
        ];
        $totals['balance'] = max(0, $totals['due'] - $totals['paid']);

        $classes = SchoolClass::orderBy('sort_order')->orderBy('name')->get();

        return view('finance.balances', compact('students', 'balances', 'totals', 'classes', 'classId', 'search'));
    }
}
