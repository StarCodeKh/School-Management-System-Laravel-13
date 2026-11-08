<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Enums\PayrollStatus;
use App\Enums\Role;
use App\Models\Payroll;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Monthly salary records for staff (admins, teachers and staff accounts). */
class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['month' => 'nullable|date_format:Y-m']);
        $month = $request->query('month', now()->format('Y-m'));

        $payrolls = Payroll::with('user')->where('month', $month)->get()->sortBy('user.name');
        $totals = [
            'net' => $payrolls->sum('net_salary'),
            'paid' => $payrolls->where('status', PayrollStatus::Paid)->sum('net_salary'),
            'pending' => $payrolls->where('status', PayrollStatus::Pending)->sum('net_salary'),
        ];
        $missingStaff = $this->staff()->whereNotIn('id', $payrolls->pluck('user_id'))->count();

        return view('finance.payroll.index', compact('payrolls', 'month', 'totals', 'missingStaff'));
    }

    public function create(Request $request)
    {
        return view('finance.payroll.form', [
            'payroll' => null,
            'staff' => $this->staff()->pluck('name', 'id'),
            'month' => $request->query('month', now()->format('Y-m')),
        ]);
    }

    public function store(Request $request)
    {
        $payroll = Payroll::create($this->validated($request));

        return redirect()->route('payroll.index', ['month' => $payroll->month])->with('success', 'Salary record created.');
    }

    public function edit(Payroll $payroll)
    {
        return view('finance.payroll.form', ['payroll' => $payroll, 'staff' => $this->staff()->pluck('name', 'id'), 'month' => $payroll->month]);
    }

    public function update(Request $request, Payroll $payroll)
    {
        $payroll->update($this->validated($request, $payroll));

        return redirect()->route('payroll.index', ['month' => $payroll->month])->with('success', 'Salary record updated.');
    }

    public function destroy(Payroll $payroll)
    {
        $payroll->delete();

        return redirect()->route('payroll.index', ['month' => $payroll->month])->with('success', 'Salary record deleted.');
    }

    public function markPaid(Request $request, Payroll $payroll)
    {
        $validated = $request->validate(['payment_method' => ['required', Rule::enum(PaymentMethod::class)]]);

        $payroll->update(['status' => PayrollStatus::Paid, 'paid_on' => today(), 'payment_method' => $validated['payment_method']]);

        return redirect()->back()->with('success', "Salary of {$payroll->user->name} marked as paid.");
    }

    /** create pending records for staff who have none this month, copying their most recent salary */
    public function generate(Request $request)
    {
        $month = $request->validate(['month' => 'required|date_format:Y-m'])['month'];
        $created = 0;

        foreach ($this->staff()->whereDoesntHave('payrolls', fn ($q) => $q->where('month', $month))->get() as $user) {
            $previous = $user->payrolls()->where('month', '<', $month)->orderByDesc('month')->first();
            if (! $previous) {
                continue; // no salary on record yet: add this person manually once
            }

            Payroll::create([
                'user_id' => $user->id, 'month' => $month, 'status' => PayrollStatus::Pending,
                'basic_salary' => $previous->basic_salary, 'allowances' => $previous->allowances, 'deductions' => $previous->deductions,
            ]);
            $created++;
        }

        return redirect()->route('payroll.index', ['month' => $month])
            ->with($created ? 'success' : 'info', $created ? "Created {$created} salary records from previous months." : 'Nothing to copy: every staff member with a previous salary already has a record.');
    }

    private function staff()
    {
        return User::whereIn('role', Role::staff())->orderBy('name');
    }

    private function validated(Request $request, ?Payroll $ignore = null): array
    {
        $validated = $request->validate([
            'user_id' => ['required', Rule::exists('users', 'id')->whereIn('role', array_map(fn ($role) => $role->value, Role::staff()))],
            'month' => ['required', 'date_format:Y-m', Rule::unique('payrolls')->where('user_id', $request->input('user_id'))->ignore($ignore?->id)],
            'basic_salary' => 'required|numeric|min:0|max:9999999',
            'allowances' => 'nullable|numeric|min:0|max:9999999',
            'deductions' => 'nullable|numeric|min:0|max:9999999',
            'status' => ['required', Rule::enum(PayrollStatus::class)],
            'paid_on' => 'nullable|required_if:status,paid|date',
            'payment_method' => ['nullable', 'required_if:status,paid', Rule::enum(PaymentMethod::class)],
            'notes' => 'nullable|string|max:1000',
        ], ['month.unique' => 'This staff member already has a salary record for that month.']);

        return $validated + ['allowances' => 0, 'deductions' => 0];
    }
}
