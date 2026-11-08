<?php

namespace App\Http\Controllers;

use App\Enums\GuardianRelationship;
use App\Enums\Role;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Link parent accounts to students. */
class GuardianController extends Controller
{
    public function store(Request $request, Student $student)
    {
        $validated = $request->validate([
            'user_id' => [
                'required',
                Rule::exists('users', 'id')->where('role', Role::Parent->value),
                Rule::unique('guardian_student')->where('student_id', $student->id),
            ],
            'relationship' => ['required', Rule::enum(GuardianRelationship::class)],
        ], ['user_id.unique' => 'This parent is already linked to the student.', 'user_id.exists' => 'Choose an account with the Parent role.']);

        $student->guardians()->attach($validated['user_id'], ['relationship' => $validated['relationship']]);

        return redirect()->back()->with('success', 'Parent linked.');
    }

    public function destroy(Student $student, User $guardian)
    {
        $student->guardians()->detach($guardian->id);

        return redirect()->back()->with('success', 'Parent unlinked.');
    }
}
