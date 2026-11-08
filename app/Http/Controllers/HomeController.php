<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Enums\Role;
use App\Enums\Weekday;
use App\Models\Department;
use App\Models\FeeCollection;
use App\Models\Invoice;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TimetableEntry;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /** admin dashboard; other roles are sent to their own dashboard */
    public function index(Request $request)
    {
        $user = $request->user();
        if (! $user->isAdmin()) {
            return match (true) {
                $user->hasRole(Role::Teacher) => redirect()->route('teacher/dashboard'),
                $user->hasRole(Role::Student) => redirect()->route('student/dashboard'),
                default => redirect()->route('user/profile/page'),
            };
        }

        $stats = [
            'students' => Student::count(),
            'teachers' => Teacher::count(),
            'departments' => Department::count(),
            'revenue' => FeeCollection::sum('amount'),
        ];

        $recentStudents = Student::with(['schoolClass', 'section'])->latest()->take(5)->get();
        $recentPayments = FeeCollection::with(['student', 'feeType'])->latest('paid_date')->take(5)->get();
        $classes = SchoolClass::withCount('students')->orderBy('sort_order')->orderBy('name')->get();
        $unpaidInvoices = Invoice::whereIn('status', [InvoiceStatus::Draft, InvoiceStatus::Sent])->count();

        return view('dashboard.home', compact('stats', 'recentStudents', 'recentPayments', 'classes', 'unpaidInvoices'));
    }

    /** profile user */
    public function userProfile()
    {
        return view('dashboard.profile');
    }

    /** dashboard for the logged-in teacher */
    public function teacherDashboardIndex(Request $request)
    {
        $teacher = $request->user()->teacher?->load(['department.head', 'department.teachers.user']);

        $stats = [
            'students' => Student::count(),
            'classes' => SchoolClass::count(),
            'subjects' => Subject::count(),
            'colleagues' => $teacher?->department ? $teacher->department->teachers->count() - 1 : 0,
        ];

        $headedDepartments = $request->user()->headedDepartments()->withCount('teachers')->get();
        $todayClasses = $teacher && Weekday::today()
            ? $teacher->timetableEntries()->with(['section.schoolClass', 'subject'])->where('day_of_week', Weekday::today())->get()
            : collect();
        $classes = SchoolClass::with('sections')->withCount(['students', 'subjects'])->orderBy('sort_order')->orderBy('name')->get();

        return view('dashboard.teacher_dashboard', compact('teacher', 'stats', 'headedDepartments', 'classes', 'todayClasses'));
    }

    /** dashboard for the logged-in student */
    public function studentDashboardIndex(Request $request)
    {
        $student = $request->user()->student?->load(['schoolClass', 'section', 'feeCollections.feeType']);

        $subjects = $student ? Subject::where('school_class_id', $student->school_class_id)->orderBy('subject_name')->get() : collect();
        $classmates = $student ? Student::where('section_id', $student->section_id)->whereKeyNot($student->id)->count() : 0;
        $invoices = $request->user()->invoices()->latest()->take(5)->get();
        $outstanding = $request->user()->invoices()->whereIn('status', [InvoiceStatus::Draft, InvoiceStatus::Sent])->sum('total_amount');

        $academics = $this->academicSummary($student);

        return view('dashboard.student_dashboard', compact('student', 'subjects', 'classmates', 'invoices', 'outstanding') + $academics);
    }

    /** overview data shared with the student profile, plus today's timetable */
    private function academicSummary(?Student $student): array
    {
        if (! $student) {
            return ['fees' => null, 'todayTimetable' => collect()];
        }

        return StudentController::overview($student) + [
            'todayTimetable' => Weekday::today()
                ? TimetableEntry::with(['subject', 'teacher.user'])->where('section_id', $student->section_id)->where('day_of_week', Weekday::today())->orderBy('period')->get()
                : collect(),
        ];
    }
}
