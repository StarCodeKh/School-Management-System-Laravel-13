@php
    $accounts = $users->mapWithKeys(fn ($user) => [$user->id => ['name' => $user->name, 'email' => $user->email, 'code' => $user->code, 'avatar' => $user->avatar_url]]);
@endphp

<x-layouts.app title="Add Teacher">
    <x-page-header title="Add Teacher" :breadcrumbs="['Teachers' => route('teacher/list/page'), 'Add Teacher' => null]" />

    {{-- the preview card fills in as the form is typed --}}
    <div class="grid items-start gap-6 xl:grid-cols-[19rem_minmax(0,1fr)]"
         x-data="{
             accounts: @js($accounts), userId: @js((string) old('user_id', '')),
             departments: @js($departments->pluck('department_name', 'id')), departmentId: @js((string) old('department_id', '')),
             qualification: @js(old('qualification', '')), experience: @js((string) old('experience', '0')), phone: @js(old('phone_number', '')),
             get account() { return this.accounts[this.userId] ?? null },
         }"
         @input="({ qualification: () => qualification = $event.target.value, experience: () => experience = $event.target.value, phone_number: () => phone = $event.target.value })[$event.target.name]?.()"
         @change="({ department_id: () => departmentId = $event.target.value })[$event.target.name]?.()">

        <x-profile-summary class="xl:sticky xl:top-24" :image="asset('images/photo_defaults.jpg')" :image-bind="'account?.avatar ?? '.\Illuminate\Support\Js::from(asset('images/photo_defaults.jpg'))"
                           name="" name-bind="account?.name" name-fallback="Choose an account" subtitle-bind="account?.email ?? 'The name and email come from the account'"
                           :details="[
                               'User ID' => ['bind' => 'account?.code', 'value' => null],
                               'Qualification' => ['bind' => 'qualification', 'value' => null],
                               'Experience' => ['bind' => 'experience + (Number(experience) === 1 ? \' year\' : \' years\')', 'value' => '0 years'],
                               'Phone' => ['bind' => 'phone', 'value' => null],
                           ]">
            <x-slot:badges>
                <x-badge color="indigo">New</x-badge>
                <x-badge color="blue" x-text="departments[departmentId] ?? 'No department'">No department</x-badge>
            </x-slot:badges>
            <x-form-progress form="add-teacher-form" />
        </x-profile-summary>

        <x-card>
            <form method="POST" action="{{ route('teacher/save') }}" id="add-teacher-form" class="space-y-6">
                @csrf
                <x-form.section title="Account" description="Teachers are linked to a user account with the Teacher role. The name and email come from that account." icon="user">
                    <x-form.select name="user_id" label="Teacher account" :options="$users->pluck('name', 'id')" placeholder="Select account" required x-model="userId" icon="user"
                                   :hint="$users->isEmpty() ? 'Every Teacher account already has a profile. Register a new Teacher account first.' : null" />
                    <x-form.field label="User ID">
                        <input type="text" readonly :value="account?.code ?? ''" class="form-control bg-slate-50 text-slate-500">
                    </x-form.field>
                </x-form.section>

                @include('teacher._profile-fields', ['teacher' => null])

                <x-form.actions submit="Save teacher" :cancel="route('teacher/list/page')" />
            </form>
        </x-card>
    </div>
</x-layouts.app>
