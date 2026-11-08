<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\FeeCollection;
use App\Models\Student;
use App\Models\Teacher;

class HomeController extends Controller
{
    /** home dashboard (routes are already behind the "auth" middleware) */
    public function index()
    {
        $stats = [
            'students' => Student::count(),
            'teachers' => Teacher::count(),
            'departments' => Department::count(),
            'revenue' => FeeCollection::sum('amount'),
        ];

        $recentStudents = Student::latest()->take(5)->get();

        return view('dashboard.home', compact('stats', 'recentStudents'));
    }

    /** profile user */
    public function userProfile()
    {
        return view('dashboard.profile');
    }

    /** teacher dashboard */
    public function teacherDashboardIndex()
    {
        return view('dashboard.teacher_dashboard');
    }

    /** student dashboard */
    public function studentDashboardIndex()
    {
        return view('dashboard.student_dashboard');
    }
}
