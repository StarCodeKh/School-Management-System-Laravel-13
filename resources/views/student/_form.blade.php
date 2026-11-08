{{-- Shared by add-student and edit-student. $student is null when adding. --}}
@php
    $sectionOptions = $classes->mapWithKeys(fn ($class) => [
        'Class '.$class->name => $class->sections->mapWithKeys(fn ($section) => [$section->id => $class->name.' - '.$section->name])->all(),
    ])->all();
@endphp

<div class="space-y-8">
    <div>
        <h2 class="text-base font-semibold text-slate-900">Student information</h2>
        <div class="mt-4 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <x-form.input name="first_name" label="First name" :value="$student?->first_name" required />
            <x-form.input name="last_name" label="Last name" :value="$student?->last_name" required />
            <x-form.select name="gender" label="Gender" :options="\App\Enums\Gender::options()" :value="$student?->gender" placeholder="Select gender" required />
            <x-form.input name="date_of_birth" type="date" label="Date of birth" :value="$student?->date_of_birth" :max="now()->subDay()->toDateString()" required />
            <x-form.select name="blood_group" label="Blood group" :options="\App\Enums\BloodGroup::options()" :value="$student?->blood_group" placeholder="Select blood group" required />
            <x-form.select name="religion" label="Religion" :options="\App\Enums\Religion::options()" :value="$student?->religion" placeholder="Select religion" required />
        </div>
    </div>

    <div>
        <h2 class="text-base font-semibold text-slate-900">Enrolment</h2>
        <div class="mt-4 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <x-form.input name="admission_id" label="Admission ID" :value="$student?->admission_id" required />
            <x-form.select name="school_class_id" label="Class" :options="$classes->pluck('name', 'id')->map(fn ($name) => 'Class '.$name)" :value="$student?->school_class_id" placeholder="Select class" required
                           :hint="$classes->isEmpty() ? 'No classes yet - add them on the Classes page.' : null" />
            <x-form.select name="section_id" label="Section" :options="$sectionOptions" :value="$student?->section_id" placeholder="Select section" required hint="Must belong to the selected class." />
            <x-form.input name="roll" label="Roll number" :value="$student?->roll" required />
            <x-form.select name="user_id" label="Login account" :options="$accounts->pluck('name', 'id')" :value="$student?->user_id" placeholder="-- None --"
                           hint="Optional: a Student account that will see this record on the student dashboard." />
        </div>
    </div>

    <div>
        <h2 class="text-base font-semibold text-slate-900">Contact</h2>
        <div class="mt-4 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <x-form.input name="email" type="email" label="Email" :value="$student?->email" required />
            <x-form.input name="phone_number" type="tel" label="Phone" :value="$student?->phone_number" inputmode="numeric" required />
            <div class="flex items-end gap-4">
                @if ($student)
                    <x-avatar :src="$student->photo_url" :alt="$student->full_name" size="size-12" />
                @endif
                <x-form.input name="photo" type="file" label="Photo" accept="image/*" :required="! $student" wrapper="flex-1" hint="JPG, PNG or GIF, up to 2 MB." />
            </div>
        </div>
    </div>
</div>
