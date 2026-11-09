<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    /** index page department */
    public function indexDepartment()
    {
        $teachers = $this->teachers();

        return view('department.add-department', compact('teachers'));
    }

    /** edit record (URL uses the display code, e.g. /department/edit/DEP-00001) */
    public function editDepartment(string $code)
    {
        $department = Department::where('code', $code)->firstOrFail();
        $teachers = $this->teachers();

        return view('department.edit-department', compact('department', 'teachers'));
    }

    /** department list with search and pagination */
    public function departmentList(Request $request)
    {
        $search = trim((string) $request->query('search'));

        $departments = Department::with('head')
            ->withCount('teachers')
            ->when($search, fn ($q) => $q->where(fn ($q) => $q
                ->where('department_name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")
                ->orWhereHas('head', fn ($h) => $h->where('name', 'like', "%{$search}%"))))
            ->sortable([
                'code' => 'code',
                'name' => 'department_name',
                'head' => fn ($q, $d) => $q->orderBy(User::select('name')->whereColumn('users.id', 'departments.head_user_id'), $d),
                'teachers' => 'teachers_count',
                'started' => 'department_start_date',
                'students' => 'no_of_students',
            ], 'name')
            ->paginateFromRequest(15);

        return view('department.list-department', compact('departments', 'search'));
    }

    /** Save Record */
    public function saveRecord(Request $request)
    {
        $validated = $request->validate($this->rules());

        try {
            Department::create($validated);

            return redirect()->back()->with('success', 'Department added.');
        } catch (\Exception $e) {
            Log::error('Failed to add new Department record', ['error' => $e->getMessage()]);

            return redirect()->back()->withInput()->with('error', "Couldn't add the department. Please try again.");
        }
    }

    /** Update Record */
    public function updateRecord(Request $request)
    {
        $department = Department::where('code', $request->input('code'))->firstOrFail();
        $validated = $request->validate($this->rules($department));

        try {
            $department->update($validated);

            return redirect()->back()->with('success', 'Department updated.');
        } catch (\Exception $e) {
            Log::error('Failed to update Department record', ['error' => $e->getMessage(), 'department' => $department->code]);

            return redirect()->back()->with('error', "Couldn't update the department. Please try again.");
        }
    }

    /** Delete Record (the list's delete modal posts the primary key) */
    public function deleteRecord(Request $request)
    {
        $department = Department::find($request->input('department_id'));

        if (! $department) {
            return redirect()->back()->with('error', 'Department not found.');
        }

        $department->delete();
        Log::info('Department record deleted successfully', ['department' => $department->code]);

        return redirect()->back()->with('success', 'Department deleted.');
    }

    private function teachers()
    {
        return Teacher::with('user')->get()->sortBy('full_name');
    }

    private function rules(?Department $ignore = null): array
    {
        return [
            'department_name' => ['required', 'string', 'max:255', Rule::unique('departments')->ignore($ignore?->id)],
            // only a user with a teacher profile can be head of department
            'head_user_id' => 'nullable|exists:teachers,user_id',
            'department_start_date' => 'required|date',
            'no_of_students' => 'required|integer|min:0',
        ];
    }
}
