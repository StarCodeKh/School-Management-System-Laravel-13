{{-- Shared by add-student and edit-student. $student is null when adding. --}}
@php
    $sectionOptions = $classes->mapWithKeys(fn ($class) => [
        'Class '.$class->name => $class->sections->mapWithKeys(fn ($section) => [$section->id => $class->name.' - '.$section->name])->all(),
    ])->all();
@endphp

<div class="space-y-6" x-data="{ classId: @js((string) old('school_class_id', $student?->school_class_id ?? '')), classGroups: @js($classes->mapWithKeys(fn ($class) => [$class->id => 'Class '.$class->name])) }">
    <x-form.section title="Student information" description="Personal details as they appear on official records." icon="user" cols="3">
        <x-form.input name="first_name" label="First name" :value="$student?->first_name" required />
        <x-form.input name="last_name" label="Last name" :value="$student?->last_name" required />
        <x-form.select name="gender" label="Gender" :options="\App\Enums\Gender::options()" :value="$student?->gender" placeholder="Select gender" required />
        <x-form.input name="date_of_birth" type="date" label="Date of birth" :value="$student?->date_of_birth" :max="now()->subDay()->toDateString()" required />
        <x-form.select name="blood_group" label="Blood group" :options="\App\Enums\BloodGroup::options()" :value="$student?->blood_group" placeholder="Select blood group" required />
        <x-form.select name="religion" label="Religion" :options="\App\Enums\Religion::options()" :value="$student?->religion" placeholder="Select religion" required />
    </x-form.section>

    <x-form.section title="Enrolment" description="Class placement and the optional login account." icon="academic-cap" cols="3">
        <x-form.input name="admission_id" label="Admission ID" :value="$student?->admission_id" required />
        <x-form.select name="school_class_id" label="Class" :options="$classes->pluck('name', 'id')->map(fn ($name) => 'Class '.$name)" :value="$student?->school_class_id" placeholder="Select class" required icon="academic-cap" x-model="classId"
                       :hint="$classes->isEmpty() ? 'No classes yet - add them on the Classes page.' : null" />
        <x-form.select name="section_id" label="Section" :options="$sectionOptions" :value="$student?->section_id" placeholder="Select section" required
                       group-filter="classGroups[classId]" x-bind:disabled="! classId" hint="Choose a class first; only its sections are listed." />
        <x-form.input name="roll" label="Roll number" :value="$student?->roll" required />
        <x-form.select name="user_id" label="Login account" :options="$accounts->pluck('name', 'id')" :value="$student?->user_id" placeholder="-- None --" icon="user"
                       hint="Optional: a Student account that will see this record on the student dashboard." />
    </x-form.section>

    <x-form.section title="Contact" description="How the school reaches this student." icon="phone" cols="3">
        <x-form.input name="email" type="email" label="Email" :value="$student?->email" required />
        <x-form.input name="phone_number" type="tel" label="Phone" :value="$student?->phone_number" inputmode="numeric" required />
        <div class="flex items-end gap-4">
            @if ($student)
                <x-avatar :src="$student->photo_url" :alt="$student->full_name" size="size-12" />
            @endif
            <x-form.input name="photo" type="file" label="Photo" accept="image/*" :required="! $student" wrapper="flex-1" hint="JPG, PNG or GIF, up to 2 MB." />
        </div>
    </x-form.section>
</div>
