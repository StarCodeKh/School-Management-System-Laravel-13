<?php

namespace App\Http\Controllers;

use App\Models\FeeStructure;
use App\Models\FeeType;
use App\Models\SchoolClass;
use Illuminate\Http\Request;

/** Fees each class is expected to pay, per fee type. */
class FeeStructureController extends Controller
{
    public function index()
    {
        $classes = SchoolClass::with(['feeStructures.feeType'])->orderBy('sort_order')->orderBy('name')->get();
        $feeTypes = FeeType::orderBy('name')->pluck('name', 'id');

        return view('finance.fee-structures', compact('classes', 'feeTypes'));
    }

    /** add or change the amount of one fee type for one class */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'school_class_id' => 'required|exists:school_classes,id',
            'fee_type_id' => 'required|exists:fee_types,id',
            'amount' => 'required|numeric|min:0|max:9999999',
            'due_date' => 'nullable|date',
        ]);

        FeeStructure::updateOrCreate(
            ['school_class_id' => $validated['school_class_id'], 'fee_type_id' => $validated['fee_type_id']],
            ['amount' => $validated['amount'], 'due_date' => $validated['due_date'] ?? null],
        );

        return redirect()->route('fee-structures.index')->with('success', 'Fee structure saved.');
    }

    public function destroy(FeeStructure $feeStructure)
    {
        $feeStructure->delete();

        return redirect()->route('fee-structures.index')->with('success', 'Fee removed from the class.');
    }
}
