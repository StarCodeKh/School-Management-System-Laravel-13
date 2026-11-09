<x-layouts.app title="Add Student">
    <x-page-header title="Add Student" :breadcrumbs="['Students' => route('student/list'), 'Add Student' => null]" />

    <x-card>
        <form method="POST" action="{{ route('student/add/save') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @include('student._form', ['student' => null])
            <x-form.actions submit="Save student" :cancel="route('student/list')" />
        </form>
    </x-card>
</x-layouts.app>
