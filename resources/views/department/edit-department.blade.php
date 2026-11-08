<x-layouts.app title="Edit Department">
    <x-page-header title="Edit Department" :breadcrumbs="['Departments' => route('department/list/page'), $department->code => null]" />

    <x-card class="max-w-3xl">
        <form method="POST" action="{{ route('department/update') }}" class="space-y-6">
            @csrf
            <input type="hidden" name="code" value="{{ $department->code }}">
            <x-form.input name="code_display" label="Department ID" :value="$department->code" readonly wrapper="sm:max-w-xs" />
            @include('department._form')
            <x-form.actions submit="Update department" :cancel="route('department/list/page')" />
        </form>
    </x-card>
</x-layouts.app>
