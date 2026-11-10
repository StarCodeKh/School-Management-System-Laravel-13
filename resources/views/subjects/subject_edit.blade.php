<x-layouts.app title="Edit Subject">
    <x-page-header title="Edit Subject" :breadcrumbs="['Subjects' => route('subject/list/page'), $subjectEdit->subject_name => null]" />

    <x-card class="max-w-3xl">
        <form method="POST" action="{{ route('subject/update') }}" class="space-y-6">
            @csrf
            <input type="hidden" name="code" value="{{ $subjectEdit->code }}">
            @include('subjects._form', ['subject' => $subjectEdit])
            <x-form.actions submit="Save changes" :cancel="route('subject/list/page')" />
        </form>
    </x-card>
</x-layouts.app>
