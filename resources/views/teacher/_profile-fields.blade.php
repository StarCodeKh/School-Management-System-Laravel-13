{{-- Teacher profile fields shared by add-teacher and edit-teacher. $teacher is null when adding. --}}
<x-form.section title="Teacher details" description="Department, qualifications and contact number." icon="academic-cap" cols="3">
    <x-form.select name="department_id" label="Department" :options="$departments->pluck('department_name', 'id')" :value="$teacher?->department_id" placeholder="-- No department --" />
    <x-form.select name="gender" label="Gender" :options="\App\Enums\Gender::options()" :value="$teacher?->gender" placeholder="Select gender" required />
    <x-form.input name="date_of_birth" type="date" label="Date of birth" :value="$teacher?->date_of_birth" required />
    <x-form.input name="qualification" label="Qualification" :value="$teacher?->qualification" placeholder="e.g. MSc Mathematics" required />
    <x-form.input name="experience" type="number" min="0" max="80" label="Experience (years)" :value="$teacher?->experience ?? 0" required />
    <x-form.input name="phone_number" type="tel" label="Phone" :value="$teacher?->phone_number" required />
</x-form.section>

<x-form.section title="Address" description="Home address on file." icon="map" cols="3">
    <x-form.input name="address" label="Address" :value="$teacher?->address" wrapper="sm:col-span-2 lg:col-span-3" required />
    <x-form.input name="city" label="City" :value="$teacher?->city" required />
    <x-form.input name="state" label="State / Province" :value="$teacher?->state" required />
    <x-form.input name="zip_code" label="Zip code" :value="$teacher?->zip_code" required />
    <x-form.input name="country" label="Country" :value="$teacher?->country" required />
</x-form.section>
