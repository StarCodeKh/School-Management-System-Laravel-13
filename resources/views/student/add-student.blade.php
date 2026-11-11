@php
    // keyed by section id (flatMap alone would renumber the keys)
    $sectionLabels = $classes->flatMap(fn ($class) => $class->sections->map(fn ($section) => ['id' => $section->id, 'label' => 'Class '.$class->name.' - '.$section->name]))->pluck('label', 'id');
@endphp

<x-layouts.app title="Add Student">
    <x-page-header title="Add Student" :breadcrumbs="['Students' => route('student/list'), 'Add Student' => null]" />

    {{-- the preview card fills in as the form is typed --}}
    <div class="grid items-start gap-6 xl:grid-cols-[19rem_minmax(0,1fr)]"
         x-data="{
             first: @js(old('first_name', '')), last: @js(old('last_name', '')), admission: @js(old('admission_id', '')),
             roll: @js(old('roll', '')), email: @js(old('email', '')), phone: @js(old('phone_number', '')),
             sectionId: @js((string) old('section_id', '')), gender: '',
             sections: @js($sectionLabels), genders: @js(\App\Enums\Gender::options()),
             photo: @js(asset('images/photo_defaults.jpg')),
         }"
         @input="({ first_name: () => first = $event.target.value, last_name: () => last = $event.target.value, admission_id: () => admission = $event.target.value,
                    roll: () => roll = $event.target.value, email: () => email = $event.target.value, phone_number: () => phone = $event.target.value })[$event.target.name]?.()"
         @change="({ section_id: () => sectionId = $event.target.value, gender: () => gender = $event.target.value })[$event.target.name]?.()"
         @image-preview.window="$event.detail.name === 'photo' && (photo = $event.detail.url || @js(asset('images/photo_defaults.jpg')))">

        <x-profile-summary class="xl:sticky xl:top-24" :image="asset('images/photo_defaults.jpg')" image-bind="photo" name="" name-bind="[first, last].filter(Boolean).join(' ')"
                           name-fallback="New student" subtitle-bind="admission ? 'Admission ID ' + admission : 'No admission ID yet'"
                           :details="[
                               'Roll number' => ['bind' => 'roll', 'value' => null],
                               'Email' => ['bind' => 'email', 'value' => null],
                               'Phone' => ['bind' => 'phone', 'value' => null],
                           ]">
            <x-slot:badges>
                <x-badge color="indigo" x-text="sections[sectionId] ?? 'No class yet'">No class yet</x-badge>
                <x-badge x-show="genders[gender]" x-text="genders[gender]" x-cloak></x-badge>
            </x-slot:badges>
            <x-form-progress form="add-student-form" />
        </x-profile-summary>

        <x-card>
            <form method="POST" action="{{ route('student/add/save') }}" enctype="multipart/form-data" id="add-student-form" class="space-y-6">
                @csrf
                @include('student._form', ['student' => null])
                <x-form.actions submit="Save student" :cancel="route('student/list')" />
            </form>
        </x-card>
    </div>
</x-layouts.app>
