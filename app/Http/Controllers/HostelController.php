<?php

namespace App\Http\Controllers;

use App\Models\HostelAllocation;
use App\Models\HostelRoom;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/** Hostel rooms and student bed allocations. */
class HostelController extends Controller
{
    public function index()
    {
        $rooms = HostelRoom::with('allocations.student')->orderBy('hostel')->orderBy('room_number')->get();
        $students = Student::doesntHave('hostelAllocation')->orderBy('first_name')->get()
            ->mapWithKeys(fn (Student $student) => [$student->id => $student->full_name.' - '.$student->admission_id]);

        return view('hostel.index', [
            'rooms' => $rooms->groupBy('hostel'),
            'students' => $students,
            'roomOptions' => $rooms->filter(fn (HostelRoom $room) => $room->allocations->count() < $room->capacity)
                ->mapWithKeys(fn (HostelRoom $room) => [$room->id => $room->label().' ('.($room->capacity - $room->allocations->count()).' free)']),
            'totals' => ['beds' => $rooms->sum('capacity'), 'used' => $rooms->sum(fn ($room) => $room->allocations->count())],
        ]);
    }

    public function storeRoom(Request $request)
    {
        HostelRoom::create($this->roomRules($request));

        return back()->with('success', 'Room added.');
    }

    public function updateRoom(Request $request, HostelRoom $room)
    {
        $validated = $this->roomRules($request, $room);
        if ($validated['capacity'] < $room->allocations()->count()) {
            throw ValidationException::withMessages(['capacity' => 'More students are allocated than this capacity.']);
        }

        $room->update($validated);

        return back()->with('success', 'Room updated.');
    }

    public function destroyRoom(HostelRoom $room)
    {
        if ($room->allocations()->exists()) {
            return back()->with('error', 'Move the students out of this room before deleting it.');
        }

        $room->delete();

        return back()->with('success', 'Room deleted.');
    }

    public function allocate(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id|unique:hostel_allocations,student_id',
            'hostel_room_id' => 'required|exists:hostel_rooms,id',
        ], ['student_id.unique' => 'This student already has a hostel room.']);

        $room = HostelRoom::withCount('allocations')->findOrFail($validated['hostel_room_id']);
        if ($room->allocations_count >= $room->capacity) {
            throw ValidationException::withMessages(['hostel_room_id' => 'This room is full.']);
        }

        HostelAllocation::create($validated + ['allocated_on' => today()]);

        return back()->with('success', 'Student allocated to '.$room->label().'.');
    }

    public function deallocate(HostelAllocation $allocation)
    {
        $allocation->delete();

        return back()->with('success', 'Student moved out.');
    }

    private function roomRules(Request $request, ?HostelRoom $ignore = null): array
    {
        $validated = $request->validate([
            'hostel' => 'required|string|max:255',
            'room_number' => ['required', 'string', 'max:20', Rule::unique('hostel_rooms')->where('hostel', $request->input('hostel'))->ignore($ignore?->id)],
            'capacity' => 'required|integer|min:1|max:50',
            'fee' => 'nullable|numeric|min:0|max:999999',
        ], ['room_number.unique' => 'This room number already exists in that hostel.']);

        return ['fee' => $validated['fee'] ?? 0] + $validated;
    }
}
