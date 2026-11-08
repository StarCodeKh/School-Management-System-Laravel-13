<x-layouts.app :title="$exam ? 'Edit Exam' : 'New Exam'">
    <x-page-header :title="$exam ? 'Edit '.$exam->name : 'New Exam'" :breadcrumbs="['Exams' => route('exams.index'), ($exam ? 'Edit' : 'New') => null]" />

    <x-card class="max-w-3xl">
        <form method="POST" action="{{ $exam ? route('exams.update', $exam) : route('exams.store') }}" class="space-y-6">
            @csrf
            @if ($exam) @method('PUT') @endif
            <div class="grid gap-5 sm:grid-cols-2">
                <x-form.input name="name" label="Exam name" :value="$exam?->name" placeholder="e.g. Midterm 2026" required />
                <x-form.select name="school_class_id" label="Class" :options="$classes" :value="$exam?->school_class_id" placeholder="Select class" required />
                <x-form.input name="term" label="Term" :value="$exam?->term" placeholder="e.g. Semester 1" />
                <x-form.input name="max_marks" type="number" min="1" max="1000" label="Maximum marks per subject" :value="$exam?->max_marks ?? 100" required />
                <x-form.input name="start_date" type="date" label="Start date" :value="$exam?->start_date" required />
                <x-form.input name="end_date" type="date" label="End date" :value="$exam?->end_date" />
            </div>
            <x-form.actions :submit="$exam ? 'Update exam' : 'Create exam'" :cancel="route('exams.index')" />
        </form>
    </x-card>
</x-layouts.app>
