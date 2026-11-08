<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Models\Department;
use App\Models\FeeCollection;
use App\Models\Invoice;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /** admin dashboard (routes are already behind the "auth" middleware) */
    public function index()
    {
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
        $classes = SchoolClass::with('sections')->withCount(['students', 'subjects'])->orderBy('sort_order')->orderBy('name')->get();

        return view('dashboard.teacher_dashboard', compact('teacher', 'stats', 'headedDepartments', 'classes'));
    }

    /** dashboard for the logged-in student */
    public function studentDashboardIndex(Request $request)
    {
        $student = $request->user()->student?->load(['schoolClass', 'section', 'feeCollections.feeType']);

        $subjects = $student ? Subject::where('school_class_id', $student->school_class_id)->orderBy('subject_name')->get() : collect();
        $classmates = $student ? Student::where('section_id', $student->section_id)->whereKeyNot($student->id)->count() : 0;
        $invoices = $request->user()->invoices()->latest()->take(5)->get();
        $outstanding = $request->user()->invoices()->whereIn('status', [InvoiceStatus::Draft, InvoiceStatus::Sent])->sum('total_amount');

        return view('dashboard.student_dashboard', compact('student', 'subjects', 'classmates', 'invoices', 'outstanding'));
    }
}
