<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Attendance;
use App\Models\ExamResult;
use App\Models\FeeCollection;
use App\Models\Invoice;
use App\Models\Student;
use App\Models\TimetableEntry;
use App\Models\User;
use Database\Seeders\Demo\PeopleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DemoSeederTest extends TestCase
{
    use RefreshDatabase;

    private function seedSmallDemo(): void
    {
        $this->artisan('school:demo', ['--students' => 3, '--months' => 1, '--no-interaction' => true])->assertSuccessful();
    }

    public function test_demo_fills_every_module_with_consistent_data(): void
    {
        $this->seedSmallDemo();

        $this->assertSame(15 * 3, Student::count()); // 6 classes: 7-9 with A-B, 10-12 with A-C
        $this->assertGreaterThan(0, Attendance::count());
        $this->assertGreaterThan(0, ExamResult::count());
        $this->assertGreaterThan(0, FeeCollection::count());
        $this->assertGreaterThan(0, Invoice::count());

        // every lesson has a teacher and no teacher is in two places at once
        $this->assertSame(0, TimetableEntry::whereNull('teacher_id')->count());
        $this->assertSame(0, DB::table('timetable_entries')->select('teacher_id', 'day_of_week', 'period')
            ->groupBy('teacher_id', 'day_of_week', 'period')->havingRaw('count(*) > 1')->get()->count());

        // invoice totals match their line items (no tax/charges/discounts are hidden)
        Invoice::with('items')->get()->each(fn (Invoice $invoice) => $this->assertEqualsWithDelta(
            (float) $invoice->items->sum('amount'), (float) $invoice->taxable_amount, 0.01,
        ));
    }

    public function test_every_demo_login_reaches_its_dashboard(): void
    {
        $this->seedSmallDemo();

        foreach (array_keys(PeopleSeeder::LOGINS) as $email) {
            $user = User::where('email', $email)->firstOrFail();
            $this->post('/login', ['email' => $email, 'password' => config('school.demo.password')])->assertRedirect();
            $this->assertAuthenticatedAs($user);
            $this->followingRedirects()->get('/home')->assertOk();
            $this->post('/logout');
        }

        $this->assertTrue(User::where('email', 'parent@school.test')->first()->children()->exists());
        $this->assertNotNull(User::where('email', 'student@school.test')->first()->student);
    }

    public function test_admin_pages_render_with_demo_data(): void
    {
        $this->seedSmallDemo();
        $admin = User::where('role', Role::SuperAdmin)->firstOrFail();
        $student = Student::first();

        foreach ([
            'home', 'student/list', 'teacher/list/page', 'list/users', 'department/list/page', 'subject/list/page',
            'class/list/page', 'attendance/report?section_id='.$student->section_id, 'exams', 'timetable?section_id='.$student->section_id,
            'fees/balances', 'account/fees/collections/page', 'invoice/list/page', 'expenses', 'payroll', 'finance',
            'books', 'library/loans?tab=overdue', 'transport', 'hostel', 'notices', 'events', 'student/profile/'.$student->id,
        ] as $uri) {
            $this->actingAs($admin)->get('/'.$uri)->assertOk();
        }
    }

    public function test_demo_runs_only_once_per_database(): void
    {
        $this->seedSmallDemo();

        $this->artisan('school:demo', ['--no-interaction' => true])
            ->expectsOutputToContain('Demo data is already loaded')
            ->assertFailed();
    }

    public function test_demo_refuses_production_without_force(): void
    {
        $this->app['env'] = 'production';

        $this->artisan('school:demo', ['--no-interaction' => true])->assertFailed();
        $this->assertSame(0, Student::count());
    }
}
