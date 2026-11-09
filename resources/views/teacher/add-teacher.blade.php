<x-layouts.app title="Add Teacher">
    <x-page-header title="Add Teacher" :breadcrumbs="['Teachers' => route('teacher/list/page'), 'Add Teacher' => null]" />

    <x-card>
        <form method="POST" action="{{ route('teacher/save') }}" class="space-y-6"
              x-data="{ codes: @js($users->pluck('code', 'id')), userId: @js((string) old('user_id', '')) }">
            @csrf
            <x-form.section title="Account" description="Teachers are linked to a user account with the Teacher role. The name and email come from that account." icon="user" cols="3">
                    <x-form.select name="user_id" label="Teacher account" :options="$users->pluck('name', 'id')" placeholder="Select account" required x-model="userId" icon="user"
                                   :hint="$users->isEmpty() ? 'Every Teacher account already has a profile. Register a new Teacher account first.' : null" />
                    <x-form.field label="User ID">
                        <input type="text" readonly :value="codes[userId] ?? ''" class="block w-full rounded-lg border-slate-300 bg-slate-50 text-sm text-slate-500 shadow-sm">
                    </x-form.field>
            </x-form.section>

            @include('teacher._profile-fields', ['teacher' => null])

            <x-form.actions submit="Save teacher" :cancel="route('teacher/list/page')" />
        </form>
    </x-card>
</x-layouts.app>
