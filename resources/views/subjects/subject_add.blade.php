<x-layouts.app title="Add Subject">
    <x-page-header title="Add Subject" :breadcrumbs="['Subjects' => route('subject/list/page'), 'Add Subject' => null]" />

    <x-card class="max-w-3xl">
        <form method="POST" action="{{ route('subject/save') }}" class="space-y-6">
            @csrf
            @include('subjects._form', ['subject' => null])
            <x-form.actions submit="Save subject" :cancel="route('subject/list/page')" />
        </form>
    </x-card>
</x-layouts.app>
