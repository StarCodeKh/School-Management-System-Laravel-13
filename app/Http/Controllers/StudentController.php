<?php

namespace App\Http\Controllers;

use App\Enums\BloodGroup;
use App\Enums\Gender;
use App\Enums\Religion;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    /** index page student list */
    public function student()
    {
        $studentList = Student::with(['schoolClass', 'section'])->latest()->get();

        return view('student.student', compact('studentList'));
    }

    /** index page student grid */
    public function studentGrid()
    {
        $studentList = Student::with(['schoolClass', 'section'])->latest()->get();

        return view('student.student-grid', compact('studentList'));
    }

    /** student add page */
    public function studentAdd()
    {
        return view('student.add-student', $this->formOptions());
    }

    /** Save Record */
    public function studentSave(Request $request)
    {
        $validated = $request->validate($this->rules($request) + [
            'email' => 'required|email|max:255|unique:students,email',
            'admission_id' => 'required|string|max:50|unique:students,admission_id',
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            $validated['photo'] = $this->storePhoto($request->file('photo'));
            Student::create($validated);

            return redirect()->back()->with('success', 'Student has been added successfully!');
        } catch (\Exception $e) {
            Log::error('Student Save Failed', ['error' => $e->getMessage(), 'email' => $validated['email']]);

            return redirect()->back()->withInput()->with('error', 'Failed to add new student. Please try again.');
        }
    }

    /** View */
    public function studentEdit(Student $student)
    {
        return view('student.edit-student', ['studentEdit' => $student] + $this->formOptions());
    }

    /** Update Record */
    public function studentUpdate(Request $request)
    {
        $student = Student::findOrFail($request->input('id'));

        $validated = $request->validate($this->rules($request) + [
            'email' => ['required', 'email', 'max:255', Rule::unique('students', 'email')->ignore($student->id)],
            'admission_id' => ['required', 'string', 'max:50', Rule::unique('students', 'admission_id')->ignore($student->id)],
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            if ($request->hasFile('photo')) {
                $this->deletePhoto($student->photo);
                $validated['photo'] = $this->storePhoto($request->file('photo'));
            } else {
                unset($validated['photo']);
            }

            $student->update($validated);

            return redirect()->back()->with('success', 'Updated successfully!');
        } catch (\Exception $e) {
            Log::error('Student Update Failed', ['error' => $e->getMessage(), 'id' => $student->id]);

            return redirect()->back()->with('error', 'Failed to update record. Please try again.');
        }
    }

    /** Delete Record */
    public function studentDelete(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:students,id',
        ]);

        $student = Student::findOrFail($validated['id']);

        if ($student->feeCollections()->exists()) {
            return redirect()->back()->with('error', 'This student has fee payments on record and cannot be deleted.');
        }

        try {
            $photo = $student->photo;
            $student->delete();
            $this->deletePhoto($photo);

            return redirect()->back()->with('success', 'Student deleted successfully :)');
        } catch (\Exception $e) {
            Log::error('Student Deletion Failed', ['error' => $e->getMessage(), 'id' => $student->id]);

            return redirect()->back()->with('error', 'Failed to delete record. Please try again.');
        }
    }

    /** student profile page */
    public function studentProfile(Student $student)
    {
        $student->load(['schoolClass', 'section']);

        return view('student.student-profile', ['studentProfile' => $student]);
    }

    /** select options shared by the add and edit forms */
    private function formOptions(): array
    {
        return [
            'classes' => SchoolClass::with('sections')->orderBy('sort_order')->orderBy('name')->get(),
            'genders' => Gender::cases(),
            'bloodGroups' => BloodGroup::cases(),
            'religions' => Religion::cases(),
        ];
    }

    /** fields shared by the add and edit forms */
    private function rules(Request $request): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'gender' => ['required', Rule::enum(Gender::class)],
            'date_of_birth' => 'required|date|before:today',
            'roll' => 'required|string|max:20',
            'blood_group' => ['required', Rule::enum(BloodGroup::class)],
            'religion' => ['required', Rule::enum(Religion::class)],
            'school_class_id' => 'required|exists:school_classes,id',
            // the section must belong to the chosen class
            'section_id' => ['required', Rule::exists('sections', 'id')->where('school_class_id', $request->input('school_class_id'))],
            'phone_number' => 'required|numeric|digits_between:8,15',
        ];
    }

    private function storePhoto(UploadedFile $file): string
    {
        $filename = uniqid('student_', true).'.'.$file->extension();
        $file->move(public_path('student-photos'), $filename);

        return 'student-photos/'.$filename;
    }

    private function deletePhoto(?string $path): void
    {
        if ($path && File::exists(public_path($path))) {
            File::delete(public_path($path));
        }
    }
}
