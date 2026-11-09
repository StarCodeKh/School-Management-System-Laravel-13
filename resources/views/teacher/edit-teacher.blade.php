@php
    $user = $teacher->user;
    $departmentNames = $departments->pluck('department_name', 'id');
@endphp

<x-layouts.app title="Edit Teacher">
    <x-page-header title="Edit Teacher" :breadcrumbs="['Teachers' => route('teacher/list/page'), $teacher->full_name => null]" />

    {{-- the summary card follows the form as you type --}}
    <div class="grid items-start gap-6 xl:grid-cols-[19rem_minmax(0,1fr)]"
         x-data="{
             name: @js(old('full_name', $teacher->full_name)),
             departmentId: @js((string) old('department_id', $teacher->department_id)),
             departments: @js($departmentNames),
         }"
         @input="$event.target.name === 'full_name' && (name = $event.target.value)"
         @change="$event.target.name === 'department_id' && (departmentId = $event.target.value)">

        <x-profile-summary class="xl:sticky xl:top-24" :image="$user?->avatar_url ?? asset('images/photo_defaults.jpg')" :name="$teacher->full_name" name-bind="name"
                           :subtitle="$user?->email"
                           :details="[
                               'User ID' => $user?->code,
                               'Qualification' => $teacher->qualification,
                               'Experience' => trans_choice(':count year|:count years', $teacher->experience ?? 0),
                               'Lessons per week' => $teacher->timetable_entries_count,
                               'Last updated' => $teacher->updated_at?->diffForHumans(),
                           ]">
            <x-slot:badges>
                <x-badge color="blue" x-text="departments[departmentId] ?? 'No department'">{{ $teacher->department?->department_name ?? 'No department' }}</x-badge>
                @if ($teacher->gender)
                    <x-badge>{{ $teacher->gender->label() }}</x-badge>
                @endif
                @if ($user?->status)
                    <x-badge :color="$user->status->color()">{{ $user->status->label() }}</x-badge>
                @endif
            </x-slot:badges>
            <x-slot:links>
                @if ($user)
                    <x-profile-summary.link :href="url('view/user/edit/'.$user->code)" icon="key" note="photo, email, status">Login account</x-profile-summary.link>
                @endif
            </x-slot:links>
        </x-profile-summary>

        <x-card>
            <form method="POST" action="{{ route('teacher/update') }}" class="space-y-6">
                @csrf
                <input type="hidden" name="id" value="{{ $teacher->id }}">
                <x-form.section title="Account" description="The name is shared with the linked user account." icon="user">
                    <x-form.input name="full_name" label="Full name" :value="$teacher->full_name" required wrapper="sm:col-span-2" />
                    <x-form.input name="email_display" label="Email" :value="$user?->email" readonly icon="envelope" hint="Change it on the login account." />
                    <x-form.input name="code_display" label="User ID" :value="$user?->code" readonly icon="hashtag" />
                </x-form.section>

                @include('teacher._profile-fields')

                <x-form.actions submit="Save changes" :cancel="route('teacher/list/page')" />
            </form>
        </x-card>
    </div>
</x-layouts.app>
