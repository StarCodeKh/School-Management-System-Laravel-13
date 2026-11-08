<?php

namespace App\Http\Controllers;

use App\Enums\Weekday;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TimetableEntry;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/** Weekly timetable per section. Everyone can view; admins edit. */
class TimetableController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['section_id' => 'nullable|exists:sections,id']);
        $section = $request->filled('section_id') ? Section::with('schoolClass')->find($request->integer('section_id')) : null;

        $entries = $section
            ? $section->timetableEntries()->with(['subject', 'teacher.user'])->get()
            : collect();
        $periods = $entries->pluck('period')->merge(range(1, 6))->unique()->sort()->values();
        // [period][day] => entry
        $grid = $entries->groupBy('period')->map(fn ($row) => $row->keyBy(fn ($entry) => $entry->day_of_week->value));

        return view('timetable.index', [
            'sectionOptions' => SchoolClass::sectionOptions(),
            'section' => $section,
            'grid' => $grid,
            'periods' => $periods,
            'days' => Weekday::cases(),
            'subjects' => $section ? Subject::where('school_class_id', $section->school_class_id)->orderBy('subject_name')->pluck('subject_name', 'id') : collect(),
            'teachers' => Teacher::with('user')->get()->sortBy('full_name')->pluck('full_name', 'id'),
        ]);
    }

    /** add or replace the entry for a section/day/period */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'section_id' => 'required|exists:sections,id',
            'day_of_week' => ['required', Rule::enum(Weekday::class)],
            'period' => 'required|integer|min:1|max:12',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'nullable|exists:teachers,id',
            'room' => 'nullable|string|max:50',
        ]);

        $section = Section::findOrFail($validated['section_id']);
        if (Subject::whereKey($validated['subject_id'])->value('school_class_id') !== $section->school_class_id) {
            throw ValidationException::withMessages(['subject_id' => 'This subject is not taught in the selected class.']);
        }

        // a teacher cannot be in two sections in the same period
        if ($validated['teacher_id'] ?? null) {
            $clash = TimetableEntry::with('section.schoolClass')
                ->where('teacher_id', $validated['teacher_id'])
                ->where('day_of_week', $validated['day_of_week'])
                ->where('period', $validated['period'])
                ->where('section_id', '!=', $section->id)
                ->first();

            if ($clash) {
                throw ValidationException::withMessages(['teacher_id' => "This teacher already teaches section {$clash->section->label()} in that period."]);
            }
        }

        TimetableEntry::updateOrCreate(
            ['section_id' => $section->id, 'day_of_week' => $validated['day_of_week'], 'period' => $validated['period']],
            $validated,
        );

        return redirect()->route('timetable.index', ['section_id' => $section->id])->with('success', 'Timetable updated.');
    }

    public function destroy(TimetableEntry $timetable)
    {
        $timetable->delete();

        return redirect()->route('timetable.index', ['section_id' => $timetable->section_id])->with('success', 'Period removed.');
    }
}
