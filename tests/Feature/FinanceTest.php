<?php

namespace Tests\Feature;

use App\Enums\PayrollStatus;
use App\Enums\Role;
use App\Models\Expense;
use App\Models\FeeCollection;
use App\Models\FeeStructure;
use App\Models\FeeType;
use App\Models\Payroll;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => Role::SuperAdmin]);
    }

    public function test_fee_structure_and_payments_give_a_student_balance(): void
    {
        $student = Student::factory()->create();
        [$tuition, $exam] = [FeeType::create(['name' => 'Tuition']), FeeType::create(['name' => 'Exam'])];

        $this->actingAs($this->admin)->post('/fee-structures', ['school_class_id' => $student->school_class_id, 'fee_type_id' => $tuition->id, 'amount' => 300])->assertSessionHasNoErrors();
        $this->actingAs($this->admin)->post('/fee-structures', ['school_class_id' => $student->school_class_id, 'fee_type_id' => $exam->id, 'amount' => 50])->assertSessionHasNoErrors();
        // saving the same class + fee type updates the amount
        $this->actingAs($this->admin)->post('/fee-structures', ['school_class_id' => $student->school_class_id, 'fee_type_id' => $exam->id, 'amount' => 40]);
        $this->assertSame(2, FeeStructure::count());

        FeeCollection::create(['student_id' => $student->id, 'fee_type_id' => $tuition->id, 'amount' => 200, 'paid_date' => now()]);

        $summary = $student->feeSummary();
        $this->assertSame([340.0, 200.0, 140.0], [$summary['due'], $summary['paid'], $summary['balance']]);

        $this->actingAs($this->admin)->get('/fees/balances')->assertOk()->assertSee('$140.00')->assertSee('Partial');
        $this->actingAs($this->admin)->get('/student/profile/'.$student->id)->assertOk()->assertSee('$140.00');
    }

    public function test_expenses_are_recorded_and_totalled_by_month(): void
    {
        $this->actingAs($this->admin)->post('/expenses', [
            'title' => 'Electricity', 'category' => 'utilities', 'amount' => 120.5, 'expense_date' => today()->toDateString(), 'payment_method' => 'cash',
        ])->assertSessionHasNoErrors();
        Expense::create(['title' => 'Old', 'category' => 'other', 'amount' => 999, 'expense_date' => today()->subMonths(2), 'payment_method' => 'cash']);

        $this->actingAs($this->admin)->get('/expenses')->assertOk()->assertSee('Electricity')->assertSee('$120.50')->assertDontSee('$999.00');
        $this->actingAs($this->admin)->post('/expenses', ['title' => 'Future', 'category' => 'other', 'amount' => 1, 'expense_date' => today()->addDay()->toDateString(), 'payment_method' => 'cash'])
            ->assertSessionHasErrors('expense_date');
    }

    public function test_payroll_net_salary_is_calculated_and_copied_to_next_month(): void
    {
        $teacher = User::factory()->create(['role' => Role::Teacher]);
        $student = User::factory()->create(['role' => Role::Student]);
        $lastMonth = now()->subMonthNoOverflow()->format('Y-m');
        $thisMonth = now()->format('Y-m');

        $this->actingAs($this->admin)->post('/payroll', [
            'user_id' => $teacher->id, 'month' => $lastMonth, 'basic_salary' => 800, 'allowances' => 100, 'deductions' => 50, 'status' => 'pending',
            'net_salary' => 1, // ignored
        ])->assertSessionHasNoErrors();
        $this->assertSame('850.00', Payroll::first()->net_salary);

        // students cannot be paid a salary, and one record per person per month
        $this->actingAs($this->admin)->post('/payroll', ['user_id' => $student->id, 'month' => $lastMonth, 'basic_salary' => 1, 'status' => 'pending'])->assertSessionHasErrors('user_id');
        $this->actingAs($this->admin)->post('/payroll', ['user_id' => $teacher->id, 'month' => $lastMonth, 'basic_salary' => 1, 'status' => 'pending'])->assertSessionHasErrors('month');

        $this->actingAs($this->admin)->post('/payroll/generate', ['month' => $thisMonth])->assertSessionHasNoErrors();
        $copy = Payroll::where('month', $thisMonth)->firstOrFail();
        $this->assertSame('850.00', $copy->net_salary);
        $this->assertSame(PayrollStatus::Pending, $copy->status);

        $this->actingAs($this->admin)->post("/payroll/{$copy->id}/pay", ['payment_method' => 'bank_transfer']);
        $this->assertSame(PayrollStatus::Paid, $copy->fresh()->status);
        $this->actingAs($this->admin)->get('/finance')->assertOk()->assertSee('$850.00');
    }

    public function test_finance_pages_are_admin_only(): void
    {
        $teacher = User::factory()->create(['role' => Role::Teacher]);

        foreach (['/finance', '/fees/balances', '/fee-structures', '/expenses', '/payroll', '/invoice/list/page', '/account/fees/collections/page'] as $uri) {
            $this->actingAs($teacher)->get($uri)->assertForbidden();
            $this->actingAs($this->admin)->get($uri)->assertOk();
        }
    }
}
