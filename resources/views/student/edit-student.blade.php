@php
    $student = $studentEdit;
    // keyed by section id (flatMap alone would renumber the keys)
    $sectionLabels = $classes->flatMap(fn ($class) => $class->sections->map(fn ($section) => ['id' => $section->id, 'label' => 'Class '.$class->name.' - '.$section->name]))->pluck('label', 'id');
@endphp

<x-layouts.app title="Edit Student">
    <x-page-header title="Edit Student" :breadcrumbs="['Students' => route('student/list'), $student->full_name => url('student/profile/'.$student->id), 'Edit' => null]" />

    {{-- the summary card follows the form as you type --}}
    <div class="grid items-start gap-6 xl:grid-cols-[19rem_minmax(0,1fr)]"
         x-data="{
             first: @js(old('first_name', $student->first_name)),
             last: @js(old('last_name', $student->last_name)),
             sectionId: @js((string) old('section_id', $student->section_id)),
             sections: @js($sectionLabels),
             photo: @js($student->photo_url),
         }"
         @input="$event.target.name === 'first_name' && (first = $event.target.value); $event.target.name === 'last_name' && (last = $event.target.value)"
         @change="$event.target.name === 'section_id' && (sectionId = $event.target.value)"
         @image-preview.window="$event.detail.name === 'photo' && (photo = $event.detail.url)">

        <x-profile-summary class="xl:sticky xl:top-24" :image="$student->photo_url" image-bind="photo" :name="$student->full_name" name-bind="[first, last].filter(Boolean).join(' ')"
                           :subtitle="'Admission ID '.$student->admission_id"
                           :details="[
                               'Roll number' => $student->roll,
                               'Attendance' => $attendanceRate !== null ? $attendanceRate.'%' : 'Not recorded yet',
                               'Fee balance' => money($feeBalance),
                               'Enrolled' => $student->created_at?->format('d M Y'),
                               'Last updated' => $student->updated_at?->diffForHumans(),
                           ]">
            <x-slot:badges>
                <x-badge color="indigo" x-text="sections[sectionId] ?? 'No section'">{{ $sectionLabels[$student->section_id] ?? 'No section' }}</x-badge>
                @if ($student->gender)
                    <x-badge>{{ $student->gender->label() }}</x-badge>
                @endif
                <x-badge :color="$feeBalance > 0 ? 'amber' : 'green'">{{ $feeBalance > 0 ? 'Fees due' : 'Fees paid' }}</x-badge>
            </x-slot:badges>
            <x-slot:links>
                <x-profile-summary.link :href="url('student/profile/'.$student->id)" icon="eye">Full profile</x-profile-summary.link>
                @if ($student->user)
                    <x-profile-summary.link :href="url('view/user/edit/'.$student->user->code)" icon="key" :note="$student->user->email">Login account</x-profile-summary.link>
                @endif
                @foreach ($student->guardians as $guardian)
                    <x-profile-summary.link :href="url('view/user/edit/'.$guardian->code)" icon="users" :note="'('.ucfirst($guardian->pivot->relationship).')'">{{ $guardian->name }}</x-profile-summary.link>
                @endforeach
            </x-slot:links>
        </x-profile-summary>

        <x-card>
            <form method="POST" action="{{ route('student/update') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <input type="hidden" name="id" value="{{ $student->id }}">
                @include('student._form', ['student' => $student])
                <x-form.actions submit="Save changes" :cancel="url('student/profile/'.$student->id)" />
            </form>
        </x-card>
    </div>
</x-layouts.app>
