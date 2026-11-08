<x-layouts.app title="Edit Teacher">
    <x-page-header title="Edit Teacher" :breadcrumbs="['Teachers' => route('teacher/list/page'), $teacher->full_name => null]" />

    <x-card>
        <form method="POST" action="{{ route('teacher/update') }}" class="space-y-8">
            @csrf
            <input type="hidden" name="id" value="{{ $teacher->id }}">
            <div>
                <h2 class="text-base font-semibold text-slate-900">Account</h2>
                <div class="mt-4 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <x-form.input name="full_name" label="Full name" :value="$teacher->full_name" required hint="Updates the linked user account." />
                    <x-form.input name="email_display" label="Email" :value="$teacher->user?->email" readonly />
                    <x-form.input name="code_display" label="User ID" :value="$teacher->user?->code" readonly />
                </div>
            </div>

            @include('teacher._profile-fields')

            <x-form.actions submit="Update teacher" :cancel="route('teacher/list/page')" />
        </form>
    </x-card>
</x-layouts.app>
