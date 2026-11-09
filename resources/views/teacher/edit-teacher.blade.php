<x-layouts.app title="Edit Teacher">
    <x-page-header title="Edit Teacher" :breadcrumbs="['Teachers' => route('teacher/list/page'), $teacher->full_name => null]" />

    <x-card>
        <form method="POST" action="{{ route('teacher/update') }}" class="space-y-6">
            @csrf
            <input type="hidden" name="id" value="{{ $teacher->id }}">
            <x-form.section title="Account" description="Name and email of the linked user account." icon="user" cols="3">
                    <x-form.input name="full_name" label="Full name" :value="$teacher->full_name" required hint="Updates the linked user account." />
                    <x-form.input name="email_display" label="Email" :value="$teacher->user?->email" readonly icon="envelope" />
                    <x-form.input name="code_display" label="User ID" :value="$teacher->user?->code" readonly icon="hashtag" />
            </x-form.section>

            @include('teacher._profile-fields')

            <x-form.actions submit="Update teacher" :cancel="route('teacher/list/page')" />
        </form>
    </x-card>
</x-layouts.app>
