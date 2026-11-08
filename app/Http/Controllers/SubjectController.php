<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class SubjectController extends Controller
{
    /** index page */
    public function subjectList(Request $request)
    {
        $search = trim((string) $request->query('search'));
        $classId = $request->integer('class') ?: null;

        $subjectList = Subject::with('schoolClass')
            ->when($search, fn ($q) => $q->where(fn ($q) => $q
                ->where('subject_name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")))
            ->when($classId, fn ($q) => $q->where('school_class_id', $classId))
            ->orderBy('subject_name')
            ->paginate(15)
            ->withQueryString();

        $classes = $this->classes();

        return view('subjects.subject_list', compact('subjectList', 'search', 'classId', 'classes'));
    }

    /** subject add */
    public function subjectAdd()
    {
        $classes = $this->classes();

        return view('subjects.subject_add', compact('classes'));
    }

    /** Save Record */
    public function saveRecord(Request $request)
    {
        $validated = $request->validate($this->rules($request));

        try {
            $subject = Subject::create($validated);
            Log::info('Subject record saved successfully', ['subject' => $subject->code]);

            return redirect()->back()->with('success', 'Subject record saved successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to save Subject record', ['error' => $e->getMessage()]);

            return redirect()->back()->withInput()->with('error', 'Failed to save Subject record. Please try again.');
        }
    }

    /** subject edit view (URL uses the display code, e.g. /subject/edit/SUB-00001) */
    public function subjectEdit(string $code)
    {
        $subjectEdit = Subject::where('code', $code)->firstOrFail();
        $classes = $this->classes();

        return view('subjects.subject_edit', compact('subjectEdit', 'classes'));
    }

    /** Update Record */
    public function updateRecord(Request $request)
    {
        $subject = Subject::where('code', $request->input('code'))->firstOrFail();
        $validated = $request->validate($this->rules($request, $subject));

        try {
            $subject->update($validated);
            Log::info('Subject record updated successfully', ['subject' => $subject->code]);

            return redirect()->back()->with('success', 'Subject record updated successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to update Subject record', ['error' => $e->getMessage(), 'subject' => $subject->code]);

            return redirect()->back()->with('error', 'Failed to update Subject record. Please try again.');
        }
    }

    /** Delete Record */
    public function deleteRecord(Request $request)
    {
        $subject = Subject::where('code', $request->input('code'))->first();

        if (! $subject) {
            return redirect()->back()->with('error', 'Subject record not found!');
        }

        $subject->delete();
        Log::info('Subject record deleted', ['subject' => $subject->code]);

        return redirect()->back()->with('success', 'Subject record deleted successfully!');
    }

    private function classes()
    {
        return SchoolClass::orderBy('sort_order')->orderBy('name')->get();
    }

    /** a subject name is unique within one class */
    private function rules(Request $request, ?Subject $ignore = null): array
    {
        return [
            'subject_name' => [
                'required', 'string', 'max:255',
                Rule::unique('subjects')->where('school_class_id', $request->input('school_class_id'))->ignore($ignore?->id),
            ],
            'school_class_id' => 'required|exists:school_classes,id',
        ];
    }
}
