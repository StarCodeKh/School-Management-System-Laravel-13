<x-layouts.app title="Edit Student">
    <x-page-header title="Edit Student" :breadcrumbs="['Students' => route('student/list'), $studentEdit->full_name => url('student/profile/'.$studentEdit->id), 'Edit' => null]" />

    <x-card>
        <form method="POST" action="{{ route('student/update') }}" enctype="multipart/form-data" class="space-y-8">
            @csrf
            <input type="hidden" name="id" value="{{ $studentEdit->id }}">
            @include('student._form', ['student' => $studentEdit])
            <x-form.actions submit="Update student" :cancel="route('student/list')" />
        </form>
    </x-card>
</x-layouts.app>
