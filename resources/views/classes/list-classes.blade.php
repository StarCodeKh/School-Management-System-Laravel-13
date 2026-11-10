<x-layouts.app title="Classes">
    <x-page-header title="Classes" :breadcrumbs="['Dashboard' => route('home'), 'Classes' => null]" />

    <x-card title="Add class" class="mb-6">
        <form method="POST" action="{{ route('class/save') }}" class="grid gap-5 sm:grid-cols-2 lg:grid-cols-[1fr_2fr_8rem_auto] lg:items-start">
            @csrf
            <x-form.input name="name" label="Class name" placeholder="e.g. 10" required />
            <x-form.input name="sections" label="Sections" placeholder="Comma separated, e.g. A, B, C" required />
            <x-form.input name="sort_order" type="number" min="0" label="Sort order" :value="0" />
            <x-button icon="plus" class="lg:mt-7">Add class</x-button>
        </form>
    </x-card>

    <x-table>
        <x-slot:head>
            <tr><th>Class</th><th>Sections</th><th>Sort order</th><th>Students</th><th>Subjects</th><th class="text-right">Actions</th></tr>
        </x-slot:head>
        @forelse ($classes as $class)
            {{-- each row is its own update form; inputs join it through the form="" attribute --}}
            <tr class="align-top">
                <td class="w-32">
                    <input form="class-{{ $class->id }}" name="name" value="{{ $class->name }}" required
                           class="form-control">
                </td>
                <td class="min-w-64">
                    <input form="class-{{ $class->id }}" name="sections" value="{{ $class->sections->pluck('name')->implode(', ') }}" required
                           class="form-control">
                </td>
                <td class="w-28">
                    <input form="class-{{ $class->id }}" name="sort_order" type="number" min="0" value="{{ $class->sort_order }}"
                           class="form-control">
                </td>
                <td class="pt-5!">{{ $class->students_count }}</td>
                <td class="pt-5!">{{ $class->subjects_count }}</td>
                <td class="text-right">
                    <form id="class-{{ $class->id }}" method="POST" action="{{ route('class/update', $class) }}" class="inline">
                        @csrf
                        <x-icon-link type="submit" icon="check" label="Save changes" tone="primary" />
                    </form>
                    <x-delete-button :action="route('class/delete')" :fields="['id' => $class->id]" title="Delete class"
                                     :message="'Delete class '.$class->name.' and its sections? Classes that still have students or subjects cannot be deleted.'" />
                </td>
            </tr>
        @empty
            <x-empty-row colspan="6" message="No classes yet. Add one above." />
        @endforelse
    </x-table>
</x-layouts.app>
