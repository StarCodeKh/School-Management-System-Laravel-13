<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Enums\Role;
use App\Models\Department;
use App\Models\FeeCollection;
use App\Models\FeeType;
use App\Models\Invoice;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PagesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Invoice $paid;

    private Invoice $overdue;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['name' => 'Admin <b>X</b>', 'role' => Role::SuperAdmin]);
        $client = User::factory()->create(['name' => 'Client One', 'role' => Role::Client]);
        $teacherUser = User::factory()->create(['role' => Role::Teacher]);

        Student::factory()->create(['first_name' => 'Sok', 'last_name' => 'Dara']);
        Teacher::create([
            'user_id' => $teacherUser->id, 'gender' => 'female', 'date_of_birth' => '1990-01-01',
            'qualification' => 'MSc', 'experience' => 5, 'phone_number' => '012', 'address' => 'St 1',
            'city' => 'Phnom Penh', 'state' => 'PP', 'zip_code' => '12000', 'country' => 'Cambodia',
        ]);
        $teacher = Teacher::first();
        Department::create(['department_name' => 'Science', 'head_user_id' => $teacher->user_id, 'department_start_date' => '2020-01-01', 'no_of_students' => 10]);
        Subject::create(['subject_name' => 'Math', 'school_class_id' => SchoolClass::first()->id]);
        $fee = FeeType::create(['name' => 'Tuition']);
        FeeCollection::create(['student_id' => Student::first()->id, 'fee_type_id' => $fee->id, 'amount' => 150, 'paid_date' => now()]);

        $this->paid = $this->makeInvoice($client, ['status' => InvoiceStatus::Paid, 'total_amount' => 100, 'due_date' => now()->addWeek()]);
        $this->overdue = $this->makeInvoice($client, ['status' => InvoiceStatus::Sent, 'total_amount' => 40, 'due_date' => now()->subWeek()]);
    }

    private function makeInvoice(User $customer, array $attributes): Invoice
    {
        $invoice = Invoice::create($attributes + [
            'customer_id' => $customer->id, 'customer_name' => $customer->name, 'po_number' => 'PO-1',
            'invoice_date' => now(), 'signatory_name' => 'Admin',
        ]);
        $invoice->items()->create(['item' => 'Books', 'category' => 'Stationary', 'quantity' => 1, 'price' => 10, 'amount' => 10]);

        return $invoice;
    }

    public static function pages(): array
    {
        return array_map(fn ($uri) => [$uri], [
            'home', 'user/profile/page', 'teacher/dashboard', 'student/dashboard', 'setting/page',
            'list/users', 'student/list', 'student/grid', 'student/add/page',
            'teacher/add/page', 'teacher/list/page', 'teacher/grid/page',
            'department/list/page', 'department/add/page', 'class/list/page', 'subject/list/page', 'subject/add/page',
            'invoice/list/page', 'invoice/paid/page', 'invoice/overdue/page', 'invoice/draft/page',
            'invoice/recurring/page', 'invoice/cancelled/page', 'invoice/grid/page', 'invoice/add/page',
            'invoice/settings/page', 'invoice/settings/tax/page', 'invoice/settings/bank/page',
            'account/fees/collections/page', 'add/fees/collection/page',
        ]);
    }

    #[DataProvider('pages')]
    public function test_page_renders_for_admin(string $uri): void
    {
        $this->actingAs($this->admin)->get('/'.$uri)->assertOk();
    }

    public function test_detail_pages_render_for_admin(): void
    {
        $student = Student::first();
        $pages = [
            '/student/edit/'.$student->id,
            '/student/profile/'.$student->id,
            '/teacher/edit/'.Teacher::first()->id,
            '/department/edit/'.Department::first()->code,
            '/subject/edit/'.Subject::first()->code,
            '/view/user/edit/'.$this->admin->code,
            '/invoice/edit/'.$this->paid->invoice_number,
            '/invoice/view/'.$this->paid->invoice_number,
        ];

        foreach ($pages as $uri) {
            $this->actingAs($this->admin)->get($uri)->assertOk();
        }

        $this->actingAs($this->admin)->get('/student/edit/'.$student->id)
            ->assertSee('value="'.$student->section_id.'" selected', false);
    }

    public function test_department_datatable_shows_head_teacher_name(): void
    {
        $this->actingAs($this->admin)->getJson('/department/get-data-list?'.http_build_query([
            'draw' => 1, 'start' => 0, 'length' => 10,
            'order' => [['column' => 2, 'dir' => 'desc']],
            'columns' => [['data' => 'code'], ['data' => 'department_name'], ['data' => 'head_of_department']],
            'search' => ['value' => 'Science'],
        ]))->assertOk()
            ->assertJsonPath('iTotalDisplayRecords', 1)
            ->assertJsonPath('aaData.0.head_of_department', e(Teacher::first()->full_name));
    }

    public function test_dashboard_shows_real_counts_and_escapes_user_name(): void
    {
        $this->actingAs($this->admin)->get('/home')
            ->assertSee('$150.00')
            ->assertSee('Sok Dara')
            ->assertSee('Admin &lt;b&gt;X&lt;/b&gt;', false);
    }

    public function test_invoice_tabs_filter_by_status(): void
    {
        $this->actingAs($this->admin)->get('/invoice/paid/page')
            ->assertSee($this->paid->invoice_number)
            ->assertDontSee($this->overdue->invoice_number);

        $this->actingAs($this->admin)->get('/invoice/overdue/page')
            ->assertSee($this->overdue->invoice_number)
            ->assertSee('Overdue')
            ->assertDontSee($this->paid->invoice_number);
    }

    public function test_invoice_status_can_be_changed(): void
    {
        $this->actingAs($this->admin)
            ->post('/invoice/status/'.$this->overdue->invoice_number, ['status' => 'paid'])
            ->assertSessionHasNoErrors();

        $this->assertSame(InvoiceStatus::Paid, $this->overdue->fresh()->status);
    }

    public function test_users_datatable_ignores_unknown_sort_column_and_escapes_html(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/get-users-data?'.http_build_query([
            'draw' => 1, 'start' => 0, 'length' => 10,
            'order' => [['column' => 0, 'dir' => 'asc']],
            'columns' => [['data' => 'password']],
            'search' => ['value' => 'Admin'],
        ]))->assertOk();

        $this->assertSame(1, $response->json('iTotalDisplayRecords'));
        $this->assertSame('Admin &lt;b&gt;X&lt;/b&gt;', $response->json('aaData.0.name'));
    }

    public function test_logout_requires_post_and_ends_session(): void
    {
        $this->actingAs($this->admin)->get('/logout')->assertStatus(405);
        $this->actingAs($this->admin)->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }
}
