<?php

use App\Http\Controllers\AccountsController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\BookLoanController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FeeBalanceController;
use App\Http\Controllers\FeeStructureController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\GuardianController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HostelController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\TimetableController;
use App\Http\Controllers\TransportController;
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

    // Permissions (gates in AppServiceProvider):
    //   manage-school = admins change school records; teach = admins + teachers view them

    // top bar search (results are filtered by permission inside the controller)
    Route::get('search', SearchController::class)->middleware('throttle:60,1')->name('search');

    // 1. HOME & SETTING
    Route::controller(HomeController::class)->group(function () {
        Route::get('/home', 'index')->name('home'); // sends each role to its own dashboard
        Route::get('user/profile/page', 'userProfile')->name('user/profile/page');
        Route::get('teacher/dashboard', 'teacherDashboardIndex')->middleware('can:teach')->name('teacher/dashboard');
        Route::get('student/dashboard', 'studentDashboardIndex')->name('student/dashboard');
        Route::get('parent/dashboard', 'parentDashboard')->middleware('can:guard-children')->name('parent/dashboard');
    });
    // settings: admins only
    Route::controller(SettingController::class)->middleware('can:manage-settings')->group(function () {
        Route::get('setting/page', 'index')->name('setting/page');
        Route::get('invoice/settings/page', 'invoice')->name('invoice/settings/page');
        Route::get('invoice/settings/tax/page', 'tax')->name('invoice/settings/tax/page');
        Route::get('invoice/settings/bank/page', 'bank')->name('invoice/settings/bank/page');
        Route::get('setting/attendance', 'attendance')->name('setting/attendance');
        Route::get('setting/library', 'library')->name('setting/library');
        Route::post('setting/{group}', 'update')->name('setting/update');
    });

    // 2. USERS
    Route::post('change/password', [UserManagementController::class, 'changePassword'])->name('change/password');
    Route::controller(UserManagementController::class)->middleware('can:manage-users')->group(function () {
        Route::get('list/users', 'index')->name('list/users');
        Route::get('view/user/edit/{code}', 'userView');
        Route::post('user/update', 'userUpdate')->name('user/update');
        Route::post('user/delete', 'userDelete')->name('user/delete');
    });

    // 3. STUDENTS
    Route::prefix('student')->controller(StudentController::class)->group(function () {
        Route::middleware('can:teach')->group(function () {
            Route::get('list', 'student')->name('student/list');
            Route::get('grid', 'studentGrid')->name('student/grid');
            Route::get('profile/{student}', 'studentProfile');
        });
        Route::middleware('can:manage-school')->group(function () {
            Route::get('add/page', 'studentAdd')->name('student/add/page');
            Route::post('add/save', 'studentSave')->name('student/add/save');
            Route::get('edit/{student}', 'studentEdit');
            Route::post('update', 'studentUpdate')->name('student/update');
            Route::post('delete', 'studentDelete')->name('student/delete');
        });
    });

    // 4. TEACHERS
    Route::prefix('teacher')->controller(TeacherController::class)->group(function () {
        Route::middleware('can:teach')->group(function () {
            Route::get('list/page', 'teacherList')->name('teacher/list/page');
            Route::get('grid/page', 'teacherGrid')->name('teacher/grid/page');
        });
        Route::middleware('can:manage-school')->group(function () {
            Route::get('add/page', 'teacherAdd')->name('teacher/add/page');
            Route::post('save', 'saveRecord')->name('teacher/save');
            Route::get('edit/{teacher}', 'editRecord');
            Route::post('update', 'updateRecordTeacher')->name('teacher/update');
            Route::post('delete', 'teacherDelete')->name('teacher/delete');
        });
    });

    // 5. DEPARTMENTS
    Route::prefix('department')->controller(DepartmentController::class)->group(function () {
        Route::get('list/page', 'departmentList')->middleware('can:teach')->name('department/list/page');
        Route::middleware('can:manage-school')->group(function () {
            Route::get('add/page', 'indexDepartment')->name('department/add/page');
            Route::get('edit/{code}', 'editDepartment');
            Route::post('save', 'saveRecord')->name('department/save');
            Route::post('update', 'updateRecord')->name('department/update');
            Route::post('delete', 'deleteRecord')->name('department/delete');
        });
    });

    // 6. CLASSES & SECTIONS
    Route::prefix('class')->controller(SchoolClassController::class)->middleware('can:manage-school')->group(function () {
        Route::get('list/page', 'index')->name('class/list/page');
        Route::post('save', 'saveRecord')->name('class/save');
        Route::post('update/{schoolClass}', 'updateRecord')->name('class/update');
        Route::post('delete', 'deleteRecord')->name('class/delete');
    });

    // 7. SUBJECTS
    Route::prefix('subject')->controller(SubjectController::class)->group(function () {
        Route::get('list/page', 'subjectList')->middleware('can:teach')->name('subject/list/page');
        Route::middleware('can:manage-school')->group(function () {
            Route::get('add/page', 'subjectAdd')->name('subject/add/page');
            Route::post('save', 'saveRecord')->name('subject/save');
            Route::get('edit/{code}', 'subjectEdit');
            Route::post('update', 'updateRecord')->name('subject/update');
            Route::post('delete', 'deleteRecord')->name('subject/delete');
        });
    });

    // 8. INVOICES
    Route::prefix('invoice')->controller(InvoiceController::class)->middleware('can:manage-finance')->group(function () {
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
    Route::controller(AccountsController::class)->middleware('can:manage-finance')->group(function () {
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

    // 11. FINANCE (admins)
    Route::middleware('can:manage-finance')->group(function () {
        Route::get('finance', [FinanceController::class, 'index'])->name('finance.index');
        Route::get('fees/balances', [FeeBalanceController::class, 'index'])->name('fees.balances');
        Route::resource('fee-structures', FeeStructureController::class)->only(['index', 'store', 'destroy']);
        Route::resource('expenses', ExpenseController::class)->except('show');
        Route::post('payroll/generate', [PayrollController::class, 'generate'])->name('payroll.generate');
        Route::post('payroll/{payroll}/pay', [PayrollController::class, 'markPaid'])->name('payroll.pay');
        Route::resource('payroll', PayrollController::class)->except('show');
    });

    // 12. COMMUNICATION: everyone reads, admins publish
    Route::resource('notices', NoticeController::class)->except(['index', 'show'])->middleware('can:manage-communication');
    Route::get('notices', [NoticeController::class, 'index'])->name('notices.index');
    Route::resource('events', EventController::class)->except(['index', 'show'])->middleware('can:manage-communication');
    Route::get('events', [EventController::class, 'index'])->name('events.index');
    Route::post('students/{student}/guardians', [GuardianController::class, 'store'])->middleware('can:manage-school')->name('guardians.store');
    Route::delete('students/{student}/guardians/{guardian}', [GuardianController::class, 'destroy'])->middleware('can:manage-school')->name('guardians.destroy');

    // 13. OPERATIONS: library, transport, hostel (admins)
    Route::middleware('can:manage-operations')->group(function () {
        Route::resource('books', BookController::class)->except('show');
        Route::get('library/loans', [BookLoanController::class, 'index'])->name('loans.index');
        Route::get('library/loans/create', [BookLoanController::class, 'create'])->name('loans.create');
        Route::post('library/loans', [BookLoanController::class, 'store'])->name('loans.store');
        Route::post('library/loans/{loan}/return', [BookLoanController::class, 'return'])->name('loans.return');

        Route::controller(TransportController::class)->prefix('transport')->name('transport.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('routes', 'storeRoute')->name('routes.store');
            Route::get('routes/{transportRoute}', 'showRoute')->name('routes.show');
            Route::put('routes/{transportRoute}', 'updateRoute')->name('routes.update');
            Route::delete('routes/{transportRoute}', 'destroyRoute')->name('routes.destroy');
            Route::post('routes/{transportRoute}/students', 'assign')->name('routes.assign');
            Route::delete('assignments/{assignment}', 'unassign')->name('assignments.destroy');
            Route::post('vehicles', 'storeVehicle')->name('vehicles.store');
            Route::put('vehicles/{vehicle}', 'updateVehicle')->name('vehicles.update');
            Route::delete('vehicles/{vehicle}', 'destroyVehicle')->name('vehicles.destroy');
        });

        Route::controller(HostelController::class)->prefix('hostel')->name('hostel.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('rooms', 'storeRoom')->name('rooms.store');
            Route::put('rooms/{room}', 'updateRoom')->name('rooms.update');
            Route::delete('rooms/{room}', 'destroyRoom')->name('rooms.destroy');
            Route::post('allocations', 'allocate')->name('allocations.store');
            Route::delete('allocations/{allocation}', 'deallocate')->name('allocations.destroy');
        });
    });
});
