<div class="grid gap-5 sm:grid-cols-2">
    <x-form.input name="subject_name" label="Subject name" :value="$subject?->subject_name" required />
    <x-form.select name="school_class_id" label="Class" :options="$classes->pluck('name', 'id')->map(fn ($name) => 'Class '.$name)" :value="$subject?->school_class_id"
                   placeholder="Select class" required :hint="$classes->isEmpty() ? 'No classes yet - add them on the Classes page.' : null" />
</div>
