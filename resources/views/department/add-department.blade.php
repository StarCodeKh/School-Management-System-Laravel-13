<x-layouts.app title="Add Department">
    <x-page-header title="Add Department" :breadcrumbs="['Departments' => route('department/list/page'), 'Add Department' => null]" />

    {{-- the preview card fills in as the form is typed --}}
    <div class="grid items-start gap-6 xl:grid-cols-[19rem_minmax(0,1fr)]"
         x-data="{
             name: @js(old('department_name', '')),
             start: @js(old('department_start_date', '')),
             headId: @js((string) old('head_user_id', '')),
             students: @js((string) old('no_of_students', '0')),
             heads: @js($teachers->pluck('full_name', 'user_id')),
             date(value) { const [y, m, d] = (value || '').split('-'); return value ? `${d} ${['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'][m - 1]} ${y}` : '' },
         }"
         @input="({ department_name: () => name = $event.target.value, no_of_students: () => students = $event.target.value })[$event.target.name]?.()"
         @change="({ department_start_date: () => start = $event.target.value, head_user_id: () => headId = $event.target.value })[$event.target.name]?.()">

        <x-profile-summary class="xl:sticky xl:top-24" icon="building" name="" name-bind="name" name-fallback="New department" subtitle="Department ID is given on save"
                           :details="[
                               'Head' => ['bind' => 'heads[headId]', 'value' => null],
                               'Started' => ['bind' => 'date(start)', 'value' => null],
                               'Students' => ['bind' => 'Number(students || 0).toLocaleString()', 'value' => '0'],
                               'Teachers' => 'Added from teacher profiles',
                           ]">
            <x-slot:badges>
                <x-badge color="indigo">New</x-badge>
                <x-badge color="green" x-show="heads[headId]" x-cloak>Head assigned</x-badge>
                <x-badge color="amber" x-show="! heads[headId]">No head yet</x-badge>
            </x-slot:badges>
            <x-form-progress form="add-department-form" />
        </x-profile-summary>

        <x-card>
            <form method="POST" action="{{ route('department/save') }}" id="add-department-form" class="space-y-6">
                @csrf
                @include('department._form', ['department' => null])
                <x-form.actions submit="Save department" :cancel="route('department/list/page')" />
            </form>
        </x-card>
    </div>
</x-layouts.app>
