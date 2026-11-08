{{-- $department is null when adding; $teachers is the list of possible heads. --}}
<div class="grid gap-5 sm:grid-cols-2">
    <x-form.input name="department_name" label="Department name" :value="$department?->department_name" required />
    <x-form.select name="head_user_id" label="Head of department" :options="$teachers->pluck('full_name', 'user_id')" :value="$department?->head_user_id"
                   placeholder="-- No head assigned --" hint="Only teachers can be head of a department." />
    <x-form.input name="department_start_date" type="date" label="Start date" :value="$department?->department_start_date" required />
    <x-form.input name="no_of_students" type="number" min="0" label="Number of students" :value="$department?->no_of_students ?? 0" required />
</div>
