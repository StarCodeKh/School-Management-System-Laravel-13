@php($student = $studentProfile)

<x-layouts.app :title="$student->full_name">
    <x-page-header :title="$student->full_name" :breadcrumbs="['Students' => route('student/list'), $student->full_name => null]">
        <x-slot:actions>
@can('manage-school')
            <x-button variant="secondary" :href="url('student/edit/'.$student->id)" icon="pencil">Edit</x-button>
@endcan
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
    <x-card title="Parents & guardians" :padding="false" class="mt-6">
        <ul class="divide-y divide-slate-100">
            @forelse ($student->guardians as $guardian)
                <li class="flex items-center gap-3 px-5 py-3 text-sm">
                    <x-avatar :src="$guardian->avatar_url" :alt="$guardian->name" size="size-8" />
                    <div class="flex-1">
                        <p class="font-medium text-slate-900">{{ $guardian->name }}</p>
                        <p class="text-xs text-slate-500">{{ $guardian->email }}@if ($guardian->phone_number) &middot; {{ $guardian->phone_number }}@endif</p>
                    </div>
                    <x-badge>{{ \App\Enums\GuardianRelationship::tryFrom($guardian->pivot->relationship)?->label() }}</x-badge>
                    @can('manage-school')
                        <x-delete-button :action="route('guardians.destroy', [$student, $guardian])" :fields="['_method' => 'DELETE']" title="Unlink parent"
                                         :message="'Unlink '.$guardian->name.' from '.$student->full_name.'? The parent account is kept.'" />
                    @endcan
                </li>
            @empty
                <li class="px-5 py-6 text-center text-sm text-slate-500">No parents linked.</li>
            @endforelse
        </ul>
        @can('manage-school')
            <form method="POST" action="{{ route('guardians.store', $student) }}" class="grid gap-4 border-t border-slate-200 p-5 sm:grid-cols-[1fr_12rem_auto] sm:items-start">
                @csrf
                <x-form.select name="user_id" label="Parent account" :options="$parentAccounts" placeholder="Select a Parent account"
                               :hint="$parentAccounts->isEmpty() ? (setting('allow_registration') ? 'No other Parent accounts. Parents can sign up on the register page as Parent.' : 'No other Parent accounts. Create one under Users.') : null" />
                <x-form.select name="relationship" label="Relationship" :options="\App\Enums\GuardianRelationship::options()" :placeholder="false" />
                <x-button icon="plus" class="sm:mt-7">Link</x-button>
            </form>
        @endcan
    </x-card>
</x-layouts.app>
