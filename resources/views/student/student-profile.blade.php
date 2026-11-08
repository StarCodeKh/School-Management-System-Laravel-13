@php($student = $studentProfile)

<x-layouts.app :title="$student->full_name">
    <x-page-header :title="$student->full_name" :breadcrumbs="['Students' => route('student/list'), $student->full_name => null]">
        <x-slot:actions>
            <x-button variant="secondary" :href="url('student/edit/'.$student->id)" icon="pencil">Edit</x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <x-card class="text-center">
            <x-avatar :src="$student->photo_url" :alt="$student->full_name" size="size-28" class="mx-auto" />
            <h2 class="mt-4 text-lg font-bold text-slate-900">{{ $student->full_name }}</h2>
            <p class="text-sm text-slate-500">{{ $student->admission_id }}</p>
            <x-badge color="indigo" class="mt-3">Class {{ $student->schoolClass?->name }} - {{ $student->section?->name }}</x-badge>
        </x-card>

        <x-card title="Personal details" class="lg:col-span-2">
            <dl class="grid gap-x-6 gap-y-5 sm:grid-cols-2">
                @foreach ([
                    'Roll number' => $student->roll,
                    'Gender' => $student->gender?->label(),
                    'Date of birth' => $student->date_of_birth?->format('d M Y'),
                    'Blood group' => $student->blood_group?->label(),
                    'Religion' => $student->religion?->label(),
                    'Email' => $student->email,
                    'Phone' => $student->phone_number,
                    'Login account' => $student->user?->email,
                    'Enrolled' => $student->created_at->format('d M Y'),
                ] as $label => $value)
                    <div>
                        <dt class="text-xs font-medium tracking-wide text-slate-500 uppercase">{{ $label }}</dt>
                        <dd class="mt-1 text-sm text-slate-900">{{ $value ?: '-' }}</dd>
                    </div>
                @endforeach
            </dl>
        </x-card>
    </div>

    <div class="mt-6">
        @include('student._overview')
    </div>
</x-layouts.app>
