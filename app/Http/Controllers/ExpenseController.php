<?php

namespace App\Http\Controllers;

use App\Enums\ExpenseCategory;
use App\Enums\PaymentMethod;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'month' => 'nullable|date_format:Y-m',
            'category' => ['nullable', Rule::enum(ExpenseCategory::class)],
        ]);
        $month = $filters['month'] ?? now()->format('Y-m');
        [$year, $monthNumber] = explode('-', $month);

        $query = Expense::query()
            ->whereYear('expense_date', $year)->whereMonth('expense_date', $monthNumber)
            ->when($filters['category'] ?? null, fn ($q, $category) => $q->where('category', $category));

        $total = (clone $query)->sum('amount');
        $byCategory = (clone $query)->selectRaw('category, SUM(amount) as total')->groupBy('category')->pluck('total', 'category');
        $expenses = $query->with('recorder')
            ->sortable([
                'date' => 'expense_date', 'title' => 'title', 'category' => 'category', 'paid_to' => 'paid_to',
                'method' => 'payment_method', 'amount' => 'amount',
            ], 'date', 'desc')
            ->paginateFromRequest(20);

        return view('finance.expenses.index', compact('expenses', 'month', 'total', 'byCategory', 'filters'));
    }

    public function create()
    {
        return view('finance.expenses.form', ['expense' => null]);
    }

    public function store(Request $request)
    {
        Expense::create($this->validated($request) + ['recorded_by' => $request->user()->id]);

        return redirect()->route('expenses.index')->with('success', 'Expense recorded.');
    }

    public function edit(Expense $expense)
    {
        return view('finance.expenses.form', compact('expense'));
    }

    public function update(Request $request, Expense $expense)
    {
        $expense->update($this->validated($request));

        return redirect()->route('expenses.index', ['month' => $expense->expense_date->format('Y-m')])->with('success', 'Expense updated.');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();

        return redirect()->back()->with('success', 'Expense deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'category' => ['required', Rule::enum(ExpenseCategory::class)],
            'amount' => 'required|numeric|min:0.01|max:99999999',
            'expense_date' => 'required|date|before_or_equal:today',
            'paid_to' => 'nullable|string|max:255',
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:2000',
        ]);
    }
}
