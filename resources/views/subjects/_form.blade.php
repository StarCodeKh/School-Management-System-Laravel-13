<x-form.section title="Subject" description="Each class has its own copy of a subject, with its own timetable and marks." icon="book">
    <x-form.input name="subject_name" label="Subject name" :value="$subject?->subject_name" required />
    <x-form.select name="school_class_id" label="Class" :options="$classes->pluck('name', 'id')->map(fn ($name) => 'Class '.$name)" :value="$subject?->school_class_id"
                   placeholder="Select class" required :hint="$classes->isEmpty() ? 'No classes yet - add them on the Classes page.' : null" />
</x-form.section>
