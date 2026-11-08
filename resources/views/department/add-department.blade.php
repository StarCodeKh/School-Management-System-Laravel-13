<x-layouts.app title="Add Department">
    <x-page-header title="Add Department" :breadcrumbs="['Departments' => route('department/list/page'), 'Add Department' => null]" />

    <x-card class="max-w-3xl">
        <form method="POST" action="{{ route('department/save') }}" class="space-y-6">
            @csrf
            @include('department._form', ['department' => null])
            <x-form.actions submit="Save department" :cancel="route('department/list/page')" />
        </form>
    </x-card>
</x-layouts.app>
