<?php

namespace App\Http\Controllers;

use App\Models\FeeCollection;
use App\Models\FeeType;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AccountsController extends Controller
{
    /** index page */
    public function index()
    {
        $feeCollections = FeeCollection::with(['student', 'feeType'])
            ->sortable([
                'admission_id' => fn ($q, $d) => $q->orderBy(Student::select('admission_id')->whereColumn('students.id', 'fee_collections.student_id'), $d),
                'student' => fn ($q, $d) => $q->orderBy(Student::select('first_name')->whereColumn('students.id', 'fee_collections.student_id'), $d),
                'fee_type' => fn ($q, $d) => $q->orderBy(FeeType::select('name')->whereColumn('fee_types.id', 'fee_collections.fee_type_id'), $d),
                'amount' => 'amount',
                'paid_date' => 'paid_date',
            ], 'paid_date', 'desc')
            ->paginateFromRequest(15);

        return view('accounts.feescollections', compact('feeCollections'));
    }

    /** add Fees Collection */
    public function addFeesCollection()
    {
        $students = Student::with(['schoolClass', 'section'])->orderBy('first_name')->orderBy('last_name')->get();
        $feeTypes = FeeType::orderBy('name')->get();
        $balances = $students->mapWithKeys(fn (Student $student) => [$student->id => money($student->feeSummary()['balance'])]);

        return view('accounts.add-fees-collection', compact('students', 'feeTypes', 'balances'));
    }

    /** Save Record */
    public function saveRecord(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'fee_type_id' => 'required|exists:fee_types,id',
            'amount' => 'required|numeric|min:0',
            'paid_date' => 'required|date',
        ]);

        try {
            $feeCollection = FeeCollection::create($validated);

            Log::info('New fees record saved successfully', ['fee_collection_id' => $feeCollection->id]);

            return redirect()->back()->with('success', 'Fee payment recorded.');
        } catch (\Exception $e) {
            Log::error('Failed to save fees record', ['error' => $e->getMessage()]);

            return redirect()->back()->with('error', "Couldn't record the fee payment. Please try again.");
        }
    }
}
