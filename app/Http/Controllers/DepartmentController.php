<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesDataTables;
use App\Models\Department;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    use HandlesDataTables;

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

    /** department list */
    public function departmentList()
    {
        return view('department.list-department');
    }

    /** get data list (DataTables server-side) */
    public function getDataList(Request $request)
    {
        $query = DB::table('departments')
            ->leftJoin('users', 'users.id', '=', 'departments.head_user_id')
            ->select('departments.*', 'users.name as head_of_department');

        $sortable = [
            'code' => 'departments.code',
            'department_name' => 'departments.department_name',
            'head_of_department' => 'users.name',
            'department_start_date' => 'departments.department_start_date',
            'no_of_students' => 'departments.no_of_students',
        ];

        return $this->dataTableResponse($request, $query, array_values($sortable), $sortable, fn ($record) => [
            'code' => e($record->code),
            'department_name' => e($record->department_name),
            'head_of_department' => e($record->head_of_department),
            'department_start_date' => e($record->department_start_date),
            'no_of_students' => e($record->no_of_students),
            'modify' => '
                <td class="text-end">
                    <div class="actions">
                        <a href="'.e(url('department/edit/'.$record->code)).'" class="btn btn-sm bg-danger-light">
                            <i class="far fa-edit me-2"></i>
                        </a>
                        <a class="btn btn-sm bg-danger-light delete department_id" data-bs-toggle="modal" data-department_id="'.e($record->id).'" data-bs-target="#delete">
                            <i class="fe fe-trash-2"></i>
                        </a>
                    </div>
                </td>',
        ]);
    }

    /** Save Record */
    public function saveRecord(Request $request)
    {
        $validated = $request->validate($this->rules());

        try {
            Department::create($validated);

            return redirect()->back()->with('success', 'Department has been added successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to add new Department record', ['error' => $e->getMessage()]);

            return redirect()->back()->withInput()->with('error', 'Failed to add new record. Please try again later.');
        }
    }

    /** Update Record */
    public function updateRecord(Request $request)
    {
        $department = Department::where('code', $request->input('code'))->firstOrFail();
        $validated = $request->validate($this->rules($department));

        try {
            $department->update($validated);

            return redirect()->back()->with('success', 'Department record updated successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to update Department record', ['error' => $e->getMessage(), 'department' => $department->code]);

            return redirect()->back()->with('error', 'Failed to update the department record. Please try again later.');
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

        return redirect()->back()->with('success', 'Department record deleted successfully!');
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
