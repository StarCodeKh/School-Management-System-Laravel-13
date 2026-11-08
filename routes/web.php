<?php

use App\Http\Controllers\AccountsController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\TimetableController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::redirect('/', '/home');

// ---------------------------- AUTHENTICATION ------------------------------//
// Login / register are custom; Auth::routes() only supplies password reset + confirm.
Auth::routes(['login' => false, 'logout' => false, 'register' => false]);

Route::middleware('guest')->group(function () {
    Route::controller(LoginController::class)->group(function () {
        Route::get('/login', 'login')->name('login');
        Route::post('/login', 'authenticate')->middleware('throttle:5,1');
    });

    Route::controller(RegisterController::class)->group(function () {
        Route::get('/register', 'register')->name('register');
        Route::post('/register', 'storeUser');
    });
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// ---------------------- APP (login required for everything) ----------------------//
Route::middleware('auth')->group(function () {

    // 1. HOME & SETTING
    Route::controller(HomeController::class)->group(function () {
        Route::get('/home', 'index')->name('home');
        Route::get('user/profile/page', 'userProfile')->name('user/profile/page');
        Route::get('teacher/dashboard', 'teacherDashboardIndex')->name('teacher/dashboard');
        Route::get('student/dashboard', 'studentDashboardIndex')->name('student/dashboard');
    });
    // settings: admins only
    Route::controller(SettingController::class)->middleware('can:manage-settings')->group(function () {
        Route::get('setting/page', 'index')->name('setting/page');
        Route::get('invoice/settings/page', 'invoice')->name('invoice/settings/page');
        Route::get('invoice/settings/tax/page', 'tax')->name('invoice/settings/tax/page');
        Route::get('invoice/settings/bank/page', 'bank')->name('invoice/settings/bank/page');
        Route::post('setting/{group}', 'update')->name('setting/update');
    });

    // 2. USERS
    Route::controller(UserManagementController::class)->group(function () {
        Route::post('change/password', 'changePassword')->name('change/password');
        Route::get('list/users', 'index')->name('list/users');
        Route::get('view/user/edit/{code}', 'userView');
        Route::post('user/update', 'userUpdate')->name('user/update');
        Route::post('user/delete', 'userDelete')->name('user/delete');
    });

    // 3. STUDENTS
    Route::prefix('student')->controller(StudentController::class)->group(function () {
        Route::get('list', 'student')->name('student/list');
        Route::get('grid', 'studentGrid')->name('student/grid');
        Route::get('add/page', 'studentAdd')->name('student/add/page');
        Route::post('add/save', 'studentSave')->name('student/add/save');
        Route::get('edit/{student}', 'studentEdit');
        Route::post('update', 'studentUpdate')->name('student/update');
        Route::post('delete', 'studentDelete')->name('student/delete');
        Route::get('profile/{student}', 'studentProfile');
    });

    // 4. TEACHERS
    Route::prefix('teacher')->controller(TeacherController::class)->group(function () {
        Route::get('add/page', 'teacherAdd')->name('teacher/add/page');
        Route::get('list/page', 'teacherList')->name('teacher/list/page');
        Route::get('grid/page', 'teacherGrid')->name('teacher/grid/page');
        Route::post('save', 'saveRecord')->name('teacher/save');
        Route::get('edit/{teacher}', 'editRecord');
        Route::post('update', 'updateRecordTeacher')->name('teacher/update');
        Route::post('delete', 'teacherDelete')->name('teacher/delete');
    });

    // 5. DEPARTMENTS
    Route::prefix('department')->controller(DepartmentController::class)->group(function () {
        Route::get('list/page', 'departmentList')->name('department/list/page');
        Route::get('add/page', 'indexDepartment')->name('department/add/page');
        Route::get('edit/{code}', 'editDepartment');
        Route::post('save', 'saveRecord')->name('department/save');
        Route::post('update', 'updateRecord')->name('department/update');
        Route::post('delete', 'deleteRecord')->name('department/delete');
    });

    // 6. CLASSES & SECTIONS
    Route::prefix('class')->controller(SchoolClassController::class)->group(function () {
        Route::get('list/page', 'index')->name('class/list/page');
        Route::post('save', 'saveRecord')->name('class/save');
        Route::post('update/{schoolClass}', 'updateRecord')->name('class/update');
        Route::post('delete', 'deleteRecord')->name('class/delete');
    });

    // 7. SUBJECTS
    Route::prefix('subject')->controller(SubjectController::class)->group(function () {
        Route::get('list/page', 'subjectList')->name('subject/list/page');
        Route::get('add/page', 'subjectAdd')->name('subject/add/page');
        Route::post('save', 'saveRecord')->name('subject/save');
        Route::get('edit/{code}', 'subjectEdit');
        Route::post('update', 'updateRecord')->name('subject/update');
        Route::post('delete', 'deleteRecord')->name('subject/delete');
    });

    // 8. INVOICES
    Route::prefix('invoice')->controller(InvoiceController::class)->group(function () {
        Route::get('list/page', 'invoiceList')->name('invoice/list/page');
        Route::get('paid/page', 'invoicePaid')->name('invoice/paid/page');
        Route::get('overdue/page', 'invoiceOverdue')->name('invoice/overdue/page');
        Route::get('draft/page', 'invoiceDraft')->name('invoice/draft/page');
        Route::get('recurring/page', 'invoiceRecurring')->name('invoice/recurring/page');
        Route::get('cancelled/page', 'invoiceCancelled')->name('invoice/cancelled/page');
        Route::get('grid/page', 'invoiceGrid')->name('invoice/grid/page');
        Route::get('add/page', 'invoiceAdd')->name('invoice/add/page');
        Route::post('add/save', 'saveRecord')->name('invoice/add/save');
        Route::get('edit/{invoice}', 'invoiceEdit')->name('invoice/edit/page');
        Route::post('update/save', 'updateRecord')->name('invoice/update/save');
        Route::post('delete', 'deleteRecord')->name('invoice/delete');
        Route::post('status/{invoice}', 'updateStatus')->name('invoice/status');
        Route::get('view/{invoice}', 'invoiceView')->name('invoice/view/page');
    });

    // 9. ACCOUNTS
    Route::controller(AccountsController::class)->group(function () {
        Route::get('account/fees/collections/page', 'index')->name('account/fees/collections/page');
        Route::get('add/fees/collection/page', 'addFeesCollection')->name('add/fees/collection/page');
        Route::post('fees/collection/save', 'saveRecord')->name('fees/collection/save');
    });

    // 10. ACADEMICS: attendance, exams and timetable
    Route::middleware('can:teach')->group(function () {
        Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('attendance', [AttendanceController::class, 'store'])->name('attendance.store');
        Route::get('attendance/report', [AttendanceController::class, 'report'])->name('attendance.report');

        Route::get('exams/{exam}/marks', [ExamController::class, 'marks'])->name('exams.marks');
        Route::post('exams/{exam}/marks', [ExamController::class, 'saveMarks'])->name('exams.marks.save');
    });
    // create/edit first, so "exams/create" is not taken as an exam id by exams/{exam}
    Route::resource('exams', ExamController::class)->except(['index', 'show'])->middleware('can:manage-academics');
    Route::resource('exams', ExamController::class)->only(['index', 'show'])->middleware('can:teach');
    Route::get('exams/{exam}/report-card/{student}', [ExamController::class, 'reportCard'])->name('exams.report-card');

    Route::get('timetable', [TimetableController::class, 'index'])->name('timetable.index');
    Route::post('timetable', [TimetableController::class, 'store'])->middleware('can:manage-academics')->name('timetable.store');
    Route::delete('timetable/{timetable}', [TimetableController::class, 'destroy'])->middleware('can:manage-academics')->name('timetable.destroy');
});
