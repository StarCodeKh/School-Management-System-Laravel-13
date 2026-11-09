<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/** Classes (grade levels) and their sections, used by the student and subject forms. */
class SchoolClassController extends Controller
{
    /** list + add form */
    public function index()
    {
        $classes = SchoolClass::with('sections')
            ->withCount(['students', 'subjects'])
            ->orderBy('sort_order')->orderBy('name')
            ->get();

        return view('classes.list-classes', compact('classes'));
    }

    /** Save Record */
    public function saveRecord(Request $request)
    {
        $validated = $request->validate($this->rules());

        DB::transaction(function () use ($validated) {
            $class = SchoolClass::create($validated);
            $class->sections()->createMany(
                collect($this->sectionNames($validated['sections']))->map(fn ($name) => ['name' => $name])->all()
            );
        });

        return redirect()->back()->with('success', 'Class added.');
    }

    /** Update Record: renames the class and syncs its sections */
    public function updateRecord(Request $request, SchoolClass $schoolClass)
    {
        $validated = $request->validate($this->rules($schoolClass));
        $names = $this->sectionNames($validated['sections']);

        $removed = $schoolClass->sections()->whereNotIn('name', $names)->withCount('students')->get();
        if ($inUse = $removed->firstWhere('students_count', '>', 0)) {
            return redirect()->back()->with('error', "Section {$inUse->name} still has students and cannot be removed.");
        }

        DB::transaction(function () use ($schoolClass, $validated, $names, $removed) {
            $schoolClass->update($validated);
            $schoolClass->sections()->whereIn('id', $removed->pluck('id'))->delete();
            foreach ($names as $name) {
                $schoolClass->sections()->firstOrCreate(['name' => $name]);
            }
        });

        return redirect()->back()->with('success', 'Class updated.');
    }

    /** Delete Record (blocked while students or subjects still use the class) */
    public function deleteRecord(Request $request)
    {
        $class = SchoolClass::withCount(['students', 'subjects'])->findOrFail($request->input('id'));

        if ($class->students_count || $class->subjects_count) {
            return redirect()->back()->with('error', "Class {$class->name} still has students or subjects and cannot be deleted.");
        }

        $class->delete(); // sections are removed by the foreign key cascade

        return redirect()->back()->with('success', 'Class deleted.');
    }

    private function rules(?SchoolClass $ignore = null): array
    {
        return [
            'name' => ['required', 'string', 'max:50', Rule::unique('school_classes')->ignore($ignore?->id)],
            'sort_order' => 'nullable|integer|min:0|max:65535',
            'sections' => 'required|string|max:255',
        ];
    }

    /** "A, B, c" -> ["A", "B", "C"] */
    private function sectionNames(string $input): array
    {
        return collect(explode(',', $input))
            ->map(fn ($name) => strtoupper(trim($name)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
