<?php

namespace App\Http\Controllers;

use App\Enums\Gender;
use App\Enums\Role;
use App\Models\Department;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class TeacherController extends Controller
{
    /** add teacher page */
    public function teacherAdd()
    {
        // only teacher accounts that don't have a teacher profile yet
        $users = User::where('role', Role::Teacher)->doesntHave('teacher')->orderBy('name')->get();

        $genders = Gender::cases();
        $departments = $this->departments();

        return view('teacher.add-teacher', compact('users', 'genders', 'departments'));
    }

    /** teacher list */
    public function teacherList(Request $request)
    {
        return view('teacher.list-teachers', $this->listing($request, 15));
    }

    /** teacher Grid */
    public function teacherGrid(Request $request)
    {
        return view('teacher.teachers-grid', $this->listing($request, 12));
    }

    /** teachers filtered by search text, shared by the list and grid views */
    private function listing(Request $request, int $perPage): array
    {
        $search = trim((string) $request->query('search'));

        $teachers = Teacher::with(['user', 'department'])
            ->when($search, fn ($q) => $q->where(fn ($q) => $q
                ->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))
                ->orWhere('qualification', 'like', "%{$search}%")
                ->orWhere('phone_number', 'like', "%{$search}%")))
            ->sortable([
                'code' => fn ($q, $d) => $q->orderBy(User::select('code')->whereColumn('users.id', 'teachers.user_id'), $d),
                'name' => fn ($q, $d) => $q->orderBy(User::select('name')->whereColumn('users.id', 'teachers.user_id'), $d),
                'department' => fn ($q, $d) => $q->orderBy(Department::select('department_name')->whereColumn('departments.id', 'teachers.department_id'), $d),
                'qualification' => 'qualification',
                'experience' => 'experience',
                'newest' => 'created_at',
            ], 'newest', 'desc')
            ->paginateFromRequest($perPage);

        return compact('teachers', 'search');
    }

    /** Save Record */
    public function saveRecord(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id|unique:teachers,user_id',
        ] + $this->profileRules());

        try {
            Teacher::create($validated);

            return redirect()->back()->with('success', 'Teacher added.');
        } catch (\Exception $e) {
            Log::error('Failed to save Teacher record', ['error' => $e->getMessage()]);

            return redirect()->back()->with('error', "Couldn't add the teacher. Please try again.");
        }
    }

    /** Edit Record */
    public function editRecord(Teacher $teacher)
    {
        $teacher->load(['user', 'department'])->loadCount('timetableEntries');
        $genders = Gender::cases();
        $departments = $this->departments();

        return view('teacher.edit-teacher', compact('teacher', 'genders', 'departments'));
    }

    /** Update Record */
    public function updateRecordTeacher(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:teachers,id',
            'full_name' => 'required|string|max:255',
        ] + $this->profileRules());

        try {
            $teacher = Teacher::with('user')->findOrFail($validated['id']);
            // the name is stored on the user account, not duplicated on teachers
            $teacher->user->update(['name' => $validated['full_name']]);
            $teacher->update(collect($validated)->except(['id', 'full_name'])->all());

            return redirect()->back()->with('success', 'Teacher updated.');
        } catch (\Exception $e) {
            Log::error('Failed to update Teacher record', ['error' => $e->getMessage(), 'teacher_id' => $validated['id']]);

            return redirect()->back()->with('error', "Couldn't update the teacher. Please try again.");
        }
    }

    /** Delete Record */
    public function teacherDelete(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:teachers,id',
        ]);

        try {
            Teacher::findOrFail($validated['id'])->delete();

            return redirect()->back()->with('success', 'Teacher deleted.');
        } catch (\Exception $e) {
            Log::error('Failed to delete Teacher record', ['error' => $e->getMessage(), 'teacher_id' => $validated['id']]);

            return redirect()->back()->with('error', "Couldn't delete the teacher. Please try again.");
        }
    }

    private function departments()
    {
        return Department::orderBy('department_name')->get();
    }

    /** fields shared by the add and edit forms */
    private function profileRules(): array
    {
        return [
            'department_id' => 'nullable|exists:departments,id',
            'gender' => ['required', Rule::enum(Gender::class)],
            'experience' => 'required|integer|min:0|max:80',
            'date_of_birth' => 'required|date',
            'qualification' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'zip_code' => 'required|string|max:20',
            'country' => 'required|string|max:100',
        ];
    }
}
