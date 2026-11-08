<?php

namespace App\Http\Controllers;

use App\Enums\PayrollStatus;
use App\Models\Expense;
use App\Models\FeeCollection;
use App\Models\Payroll;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/** Monthly income vs spending overview. */
class FinanceController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['month' => 'nullable|date_format:Y-m']);
        $month = $request->query('month', now()->format('Y-m'));
        $start = Carbon::createFromFormat('Y-m-d', $month.'-01')->startOfMonth();

        $feesIncome = (float) FeeCollection::whereYear('paid_date', $start->year)->whereMonth('paid_date', $start->month)->sum('amount');
        $expenses = (float) Expense::whereYear('expense_date', $start->year)->whereMonth('expense_date', $start->month)->sum('amount');
        $salaries = (float) Payroll::where('month', $month)->where('status', PayrollStatus::Paid)->sum('net_salary');
        $byCategory = Expense::whereYear('expense_date', $start->year)->whereMonth('expense_date', $start->month)
            ->selectRaw('category, SUM(amount) as total')->groupBy('category')->orderByDesc('total')->pluck('total', 'category');

        // last six months for the trend table
        $trend = collect(range(5, 0))->map(function (int $back) use ($start) {
            $date = $start->copy()->subMonthsNoOverflow($back);
            $income = (float) FeeCollection::whereYear('paid_date', $date->year)->whereMonth('paid_date', $date->month)->sum('amount');
            $spent = (float) Expense::whereYear('expense_date', $date->year)->whereMonth('expense_date', $date->month)->sum('amount')
                + (float) Payroll::where('month', $date->format('Y-m'))->where('status', PayrollStatus::Paid)->sum('net_salary');

            return ['label' => $date->format('M Y'), 'income' => $income, 'spent' => $spent, 'net' => $income - $spent];
        });

        return view('finance.index', compact('month', 'start', 'feesIncome', 'expenses', 'salaries', 'byCategory', 'trend'));
    }
}
