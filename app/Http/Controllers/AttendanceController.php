<?php

namespace App\Http\Controllers;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/** Daily attendance taken per section. */
class AttendanceController extends Controller
{
    /** register for one section and day; existing marks are pre-filled */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'section_id' => 'nullable|exists:sections,id',
            'date' => 'nullable|date|before_or_equal:today',
        ]);
        $date = $filters['date'] ?? today()->toDateString();
        $section = isset($filters['section_id']) ? Section::with('schoolClass')->find($filters['section_id']) : null;

        $students = $section
            ? $section->students()->orderBy('roll')->orderBy('first_name')->get()
            : collect();
        $records = $section
            ? Attendance::whereIn('student_id', $students->pluck('id'))->whereDate('date', $date)->get()->keyBy('student_id')
            : collect();

        return view('attendance.index', [
            'sectionOptions' => SchoolClass::sectionOptions(),
            'section' => $section,
            'date' => $date,
            'students' => $students,
            'records' => $records,
            'statuses' => AttendanceStatus::cases(),
        ]);
    }

    /** save the whole register in one go */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'section_id' => 'required|exists:sections,id',
            'date' => 'required|date|before_or_equal:today',
            'attendance' => 'required|array',
            'attendance.*.status' => ['required', Rule::enum(AttendanceStatus::class)],
            'attendance.*.remark' => 'nullable|string|max:255',
        ]);

        // only students of the chosen section can be marked
        $studentIds = Section::findOrFail($validated['section_id'])->students()->pluck('id');
        $rows = collect($validated['attendance'])->only($studentIds->all());

        DB::transaction(function () use ($rows, $validated, $request) {
            foreach ($rows as $studentId => $row) {
                // whereDate, not an exact match: SQLite keeps "Y-m-d 00:00:00" in date columns
                $record = Attendance::where('student_id', $studentId)->whereDate('date', $validated['date'])->first()
                    ?? new Attendance(['student_id' => $studentId, 'date' => $validated['date']]);

                $record->fill(['status' => $row['status'], 'remark' => $row['remark'] ?? null, 'recorded_by' => $request->user()->id])->save();
            }
        });

        return redirect()->route('attendance.index', ['section_id' => $validated['section_id'], 'date' => $validated['date']])
            ->with('success', "Attendance saved for {$rows->count()} students.");
    }

    /** per-student totals for a section over a date range */
    public function report(Request $request)
    {
        $filters = $request->validate([
            'section_id' => 'nullable|exists:sections,id',
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
        ]);
        $from = $filters['from'] ?? today()->startOfMonth()->toDateString();
        $to = $filters['to'] ?? today()->toDateString();
        $section = isset($filters['section_id']) ? Section::with('schoolClass')->find($filters['section_id']) : null;

        $students = $section
            ? $section->students()
                ->with(['attendances' => fn ($q) => $q->whereDate('date', '>=', $from)->whereDate('date', '<=', $to)])
                ->orderBy('roll')->orderBy('first_name')->get()
            : collect();

        return view('attendance.report', [
            'sectionOptions' => SchoolClass::sectionOptions(),
            'section' => $section,
            'from' => $from,
            'to' => $to,
            'students' => $students,
            'statuses' => AttendanceStatus::cases(),
        ]);
    }
}
