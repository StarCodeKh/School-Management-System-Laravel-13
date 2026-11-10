{{-- $department is null when adding; $teachers is the list of possible heads. --}}
@php
    // when editing, this department's own teachers come first
    $headOptions = $department
        ? collect([
            'Teachers in '.$department->department_name => $teachers->where('department_id', $department->id)->pluck('full_name', 'user_id')->all(),
            'Other teachers' => $teachers->where('department_id', '!=', $department->id)->pluck('full_name', 'user_id')->all(),
        ])->filter()->all()
        : $teachers->pluck('full_name', 'user_id');
@endphp

<x-form.section title="Department" description="Its name as shown in lists, menus and reports." icon="building">
    <x-form.input name="department_name" label="Department name" :value="$department?->department_name" required placeholder="e.g. Sciences" />
    <x-form.input name="department_start_date" type="date" label="Start date" :value="$department?->department_start_date" required hint="When the department was set up." />
</x-form.section>

<x-form.section title="Leadership and size" description="Who leads the department and how many students it serves." icon="users">
    <x-form.select name="head_user_id" label="Head of department" :options="$headOptions" :value="$department?->head_user_id" icon="user"
                   placeholder="-- No head assigned --" hint="Only teachers can be head of a department." />
    <x-form.input name="no_of_students" type="number" min="0" label="Number of students" :value="$department?->no_of_students ?? 0" required icon="hashtag"
                  hint="Entered by hand; students are not linked to departments." />
</x-form.section>
