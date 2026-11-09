<?php

namespace Database\Seeders\Demo;

use App\Enums\ExpenseCategory;
use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Enums\PayrollStatus;
use App\Enums\Role;
use App\Models\Expense;
use App\Models\FeeStructure;
use App\Models\FeeType;
use App\Models\Invoice;
use App\Models\Payroll;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Fee structures and payments (paid, part paid and unpaid students), invoices to customers,
 * monthly expenses and payroll: past months paid, this month pending, a few staff still missing.
 */
class FinanceSeeder extends Seeder
{
    /** item => [category, price] */
    private const INVOICE_ITEMS = [
        'School uniform set' => ['Uniforms', 28],
        'Sports uniform' => ['Uniforms', 18],
        'Textbook set' => ['Books', 45],
        'Exercise books (pack of 10)' => ['Books', 6],
        'Field trip to Angkor Wat' => ['Activities', 35],
        'Science fair materials' => ['Activities', 12],
        'Monthly lunch plan' => ['Catering', 60],
        'School bus service (term)' => ['Transport', 90],
    ];

    public function run(): void
    {
        $this->fees();
        $this->invoices();
        $this->expenses();
        $this->payroll();
    }

    private function fees(): void
    {
        $faker = DemoData::faker();
        $types = collect(['Tuition Fees', 'Exam Fees', 'Class Test'])->mapWithKeys(fn ($name) => [$name => FeeType::firstOrCreate(['name' => $name])]);
        $termStart = today()->subMonthsNoOverflow(config('school.demo.months'))->startOfMonth();

        foreach (SchoolClass::all() as $class) {
            // higher grades pay a little more
            $amounts = ['Tuition Fees' => 240 + 15 * (int) $class->name, 'Exam Fees' => 25, 'Class Test' => 10];
            foreach ($amounts as $type => $amount) {
                FeeStructure::updateOrCreate(
                    ['school_class_id' => $class->id, 'fee_type_id' => $types[$type]->id],
                    ['amount' => $amount, 'due_date' => $termStart->copy()->addDays(30)],
                );
            }
        }

        $structures = FeeStructure::all()->groupBy('school_class_id');
        $rows = [];
        foreach (Student::all(['id', 'school_class_id']) as $student) {
            // 55% paid in full, 30% part paid, 15% nothing yet
            $plan = DemoData::weighted(['full' => 55, 'partial' => 30, 'none' => 15]);
            if ($plan === 'none') {
                continue;
            }
            foreach ($structures[$student->school_class_id] ?? [] as $structure) {
                $amount = (float) $structure->amount;
                if ($plan === 'partial') {
                    if ($structure->fee_type_id !== $types['Tuition Fees']->id && $faker->boolean(50)) {
                        continue;
                    }
                    $amount = $structure->fee_type_id === $types['Tuition Fees']->id ? round($amount * $faker->randomElement([0.25, 0.5, 0.75])) : $amount;
                }
                // tuition is often paid in two instalments
                $parts = $plan === 'full' && $structure->fee_type_id === $types['Tuition Fees']->id && $faker->boolean(40) ? [0.5, 0.5] : [1];
                foreach ($parts as $share) {
                    $rows[] = [
                        'student_id' => $student->id,
                        'fee_type_id' => $structure->fee_type_id,
                        'amount' => round($amount * $share, 2),
                        'paid_date' => DemoData::dateBetween($termStart, today())->toDateString(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }
        foreach (array_chunk($rows, 1000) as $chunk) {
            DB::table('fee_collections')->insert($chunk);
        }
    }

    private function invoices(): void
    {
        $faker = DemoData::faker();
        $customers = User::whereIn('role', [Role::Client, Role::Student])->get();
        $signatory = User::where('role', Role::SuperAdmin)->value('name') ?? 'School Office';
        $taxRate = (float) setting('tax_rate', 10);
        $from = today()->subMonthsNoOverflow(config('school.demo.months'))->startOfMonth();

        foreach ($customers as $customer) {
            $count = $customer->role === Role::Client ? $faker->numberBetween(3, 5) : $faker->numberBetween(1, 2);
            for ($n = 0; $n < $count; $n++) {
                $date = DemoData::dateBetween($from, today());
                $status = InvoiceStatus::from(DemoData::weighted([
                    InvoiceStatus::Paid->value => 50, InvoiceStatus::Sent->value => 25, InvoiceStatus::Draft->value => 15, InvoiceStatus::Cancelled->value => 10,
                ]));

                $items = collect($faker->randomElements(array_keys(self::INVOICE_ITEMS), $faker->numberBetween(1, 3)))->map(function (string $name) use ($faker) {
                    [$category, $price] = self::INVOICE_ITEMS[$name];
                    $quantity = $faker->numberBetween(1, 4);
                    $discount = $faker->randomElement([0, 0, 0, 5, 10]);

                    return [
                        'item' => $name, 'category' => $category, 'quantity' => $quantity, 'price' => $price, 'discount' => $discount,
                        'amount' => round($quantity * $price * (1 - $discount / 100), 2),
                    ];
                });

                // same arithmetic as InvoiceController::invoiceAttributes
                $subtotal = $items->sum('amount');
                $enableTax = $customer->role === Role::Client;
                $taxAmount = $enableTax ? round($subtotal * $taxRate / 100, 2) : 0;
                $charges = $faker->boolean(20) ? [5.0] : [];
                $discounts = $faker->boolean(15) ? [5.0] : [];
                $discountAmount = round(array_sum(array_map(fn ($percent) => $subtotal * $percent / 100, $discounts)), 2);

                $invoice = Invoice::create([
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->name,
                    'po_number' => 'PO-'.$faker->numerify('####'),
                    'invoice_date' => $date,
                    'due_date' => $date->copy()->addDays((int) setting('invoice_due_days', 14)),
                    'status' => $status,
                    'enable_tax' => $enableTax,
                    'tax_rate' => $enableTax ? $taxRate : 0,
                    'tax_amount' => $taxAmount,
                    'is_recurring' => false,
                    'taxable_amount' => $subtotal,
                    'round_off' => 0,
                    'additional_charges' => $charges,
                    'discounts' => $discounts,
                    'total_amount' => max(0, round($subtotal + $taxAmount + array_sum($charges) - $discountAmount, 2)),
                    'terms_and_conditions' => setting('invoice_terms'),
                    'notes' => $faker->boolean(30) ? 'Thank you for your business.' : null,
                    'signatory_name' => $signatory,
                ]);
                $invoice->items()->createMany($items->all());
            }
        }
    }

    private function expenses(): void
    {
        $faker = DemoData::faker();
        $recorder = User::where('role', Role::Staff)->where('position', 'Accountant')->value('id');
        $monthly = [
            // title, category, min, max, paid to, method
            ['Electricity bill', ExpenseCategory::Utilities, 380, 620, 'Electricité du Cambodge', PaymentMethod::BankTransfer],
            ['Water bill', ExpenseCategory::Utilities, 60, 120, 'Phnom Penh Water Supply', PaymentMethod::BankTransfer],
            ['Internet service', ExpenseCategory::Utilities, 80, 80, 'Ezecom', PaymentMethod::BankTransfer],
            ['Stationery and printing', ExpenseCategory::Supplies, 60, 240, 'Mekong Book Supply', PaymentMethod::Cash],
            ['School bus fuel', ExpenseCategory::Transport, 220, 420, 'Tela Petrol Station', PaymentMethod::Card],
        ];
        $occasional = [
            ['Air-conditioner repair', ExpenseCategory::Maintenance, 90, 300, 'Cool Air Service', PaymentMethod::Cash],
            ['Classroom painting', ExpenseCategory::Maintenance, 300, 800, 'Bright Paint Co.', PaymentMethod::BankTransfer],
            ['Sports day prizes', ExpenseCategory::Events, 120, 260, 'Lucky Mall', PaymentMethod::Card],
            ['Projector for science lab', ExpenseCategory::Equipment, 350, 650, 'Tech Zone', PaymentMethod::BankTransfer],
            ['Cleaning supplies', ExpenseCategory::Supplies, 40, 110, 'Aeon Mall', PaymentMethod::Cash],
        ];

        foreach (DemoData::months(config('school.demo.months')) as $month) {
            $last = $month->isSameMonth(today()) ? today() : $month->copy()->endOfMonth();
            $entries = array_merge($monthly, $faker->randomElements($occasional, $faker->numberBetween(1, 3)));
            foreach ($entries as [$title, $category, $min, $max, $paidTo, $method]) {
                $date = DemoData::dateBetween($month, $last);
                Expense::create([
                    'title' => $title.' - '.$month->format('F'),
                    'category' => $category,
                    'amount' => $faker->numberBetween($min, $max) + $faker->randomElement([0, 0.5]),
                    'expense_date' => $date,
                    'paid_to' => $paidTo,
                    'payment_method' => $method,
                    'reference' => $method === PaymentMethod::Cash ? null : 'REF-'.$faker->numerify('######'),
                    'recorded_by' => $recorder,
                ]);
            }
        }
    }

    private function payroll(): void
    {
        $faker = DemoData::faker();
        $staff = User::whereIn('role', Role::staff())->with('teacher')->get();
        $months = DemoData::months(config('school.demo.months'));

        foreach ($staff as $i => $user) {
            $basic = match ($user->role) {
                Role::SuperAdmin => 1500,
                Role::Admin => 1200,
                Role::Teacher => 450 + 20 * min(20, $user->teacher?->experience ?? 0),
                default => $faker->randomElement([380, 420, 480, 550]),
            };
            foreach ($months as $month) {
                $current = $month->isSameMonth(today());
                // leave two people without a record this month, so "Copy from previous months" has work to do
                if ($current && $i < 2) {
                    continue;
                }
                Payroll::create([
                    'user_id' => $user->id,
                    'month' => $month->format('Y-m'),
                    'basic_salary' => $basic,
                    'allowances' => $faker->randomElement([0, 25, 50, 80]),
                    'deductions' => $faker->randomElement([0, 0, 10, 20]),
                    'status' => $current ? PayrollStatus::Pending : PayrollStatus::Paid,
                    'paid_on' => $current ? null : $month->copy()->endOfMonth()->subDays(2),
                    'payment_method' => $current ? null : PaymentMethod::BankTransfer,
                ]);
            }
        }
    }
}
