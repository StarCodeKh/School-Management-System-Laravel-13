<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Department;
use App\Models\FeeCollection;
use App\Models\FeeType;
use App\Models\Invoice;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Database\Seeders\MenuSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RedesignTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => Role::SuperAdmin]);
    }

    public function test_settings_are_saved_and_used_across_the_app(): void
    {
        $this->actingAs($this->admin)->post('/setting/general', [
            'school_name' => 'Angkor High School', 'currency_symbol' => '៛',
        ])->assertSessionHasNoErrors();

        $this->assertSame('Angkor High School', Setting::value('school_name'));
        $this->actingAs($this->admin)->get('/home')->assertSee('Angkor High School')->assertSee('៛0.00');
    }

    public function test_settings_are_admin_only(): void
    {
        $user = User::factory()->create(['role' => Role::Teacher]);

        $this->actingAs($user)->get('/setting/page')->assertForbidden();
        $this->actingAs($user)->post('/setting/tax', ['tax_name' => 'VAT', 'tax_rate' => 10])->assertForbidden();
        $this->assertSame('0', Setting::value('tax_rate'));
    }

    public function test_unknown_settings_group_is_not_found(): void
    {
        $this->actingAs($this->admin)->post('/setting/secrets', ['x' => 1])->assertNotFound();
    }

    public function test_invoice_totals_are_calculated_by_the_server(): void
    {
        Setting::store(['tax_rate' => '10']);
        $customer = User::factory()->create(['role' => Role::Client]);

        $this->actingAs($this->admin)->post('/invoice/add/save', [
            'customer_id' => $customer->id, 'po_number' => 'PO-7', 'invoice_date' => '2026-01-01', 'due_date' => '2026-01-31',
            'items' => [
                ['item' => 'Books', 'category' => 'Stationery', 'quantity' => 2, 'price' => 50, 'discount' => 10], // 90
                ['item' => 'Bag', 'category' => 'Stationery', 'quantity' => 1, 'price' => 10, 'discount' => 0],    // 10
            ],
            'enable_tax' => '1',          // +10% of 100 = 10
            'charges' => [5],             // +5
            'discounts' => [20],          // -20% of 100 = 20
            'round_off' => '0.50',
            // values a browser could tamper with are ignored
            'total_amount' => 1, 'taxable_amount' => 1,
            'signatory_name' => 'Admin',
        ])->assertSessionHasNoErrors();

        $invoice = Invoice::with('items')->firstOrFail();
        $this->assertSame(['90.00', '10.00'], $invoice->items->pluck('amount')->all());
        $this->assertSame('100.00', $invoice->taxable_amount);
        $this->assertSame('10.00', $invoice->tax_amount);
        $this->assertSame('95.50', $invoice->total_amount); // 100 + 10 + 5 - 20 + 0.5
    }

    public function test_teacher_dashboard_shows_the_teachers_own_profile(): void
    {
        $user = User::factory()->create(['name' => 'Chan Sopheap', 'role' => Role::Teacher]);
        $department = Department::create(['department_name' => 'Science', 'department_start_date' => '2020-01-01', 'no_of_students' => 0, 'head_user_id' => $user->id]);
        Teacher::create([
            'user_id' => $user->id, 'department_id' => $department->id, 'gender' => 'female', 'date_of_birth' => '1990-01-01',
            'qualification' => 'MSc Physics', 'experience' => 6, 'phone_number' => '012', 'address' => 'St 1',
            'city' => 'Siem Reap', 'state' => 'SR', 'zip_code' => '17000', 'country' => 'Cambodia',
        ]);

        $this->actingAs($user)->get('/teacher/dashboard')
            ->assertOk()->assertSee('MSc Physics')->assertSee('Siem Reap')->assertSee('Departments you lead');
    }

    public function test_student_dashboard_shows_the_linked_student_record(): void
    {
        $user = User::factory()->create(['role' => Role::Student]);
        $student = Student::factory()->create(['user_id' => $user->id, 'first_name' => 'Dara', 'last_name' => 'Kim']);
        Subject::create(['subject_name' => 'Khmer Literature', 'school_class_id' => $student->school_class_id]);
        FeeCollection::create(['student_id' => $student->id, 'fee_type_id' => FeeType::create(['name' => 'Tuition'])->id, 'amount' => 120, 'paid_date' => now()]);

        $this->actingAs($user)->get('/student/dashboard')
            ->assertOk()->assertSee('Dara Kim')->assertSee('Khmer Literature')->assertSee('$120.00');
    }

    public function test_dashboards_explain_when_no_profile_is_linked(): void
    {
        $this->actingAs($this->admin)->get('/teacher/dashboard')->assertOk()->assertSee('no teacher profile');
        $this->actingAs($this->admin)->get('/student/dashboard')->assertOk()->assertSee('not linked to a student record');
    }

    public function test_sidebar_hides_admin_menus_from_other_users(): void
    {
        $this->seed(MenuSeeder::class);
        $teacher = User::factory()->create(['role' => Role::Teacher]);

        $this->actingAs($this->admin)->get('/home')->assertSee('User Management')->assertSee('Settings');
        $this->actingAs($teacher)->get('/home')->assertSee('Students')->assertDontSee('User Management');
    }

    public function test_student_can_be_linked_to_a_login_account(): void
    {
        $student = Student::factory()->create();
        $account = User::factory()->create(['role' => Role::Student]);

        $this->actingAs($this->admin)->get('/student/edit/'.$student->id)->assertOk()->assertSee($account->name);
    }
}
