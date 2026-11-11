<x-layouts.app title="Classes">
    <x-page-header title="Classes" :breadcrumbs="['Dashboard' => route('home'), 'Classes' => null]">
        <x-slot:actions>
            <x-button type="button" icon="plus" x-data @click="$dispatch('open-modal', 'add-class')">Add class</x-button>
        </x-slot:actions>
    </x-page-header>

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
            <x-empty-row colspan="6" message="No classes yet. Use Add class to create one." />
        @endforelse
    </x-table>

    <x-modal name="add-class" title="Add class" description="A grade and its sections, e.g. class 10 with sections A, B and C." icon="squares" size="sm">
        <form method="POST" action="{{ route('class/save') }}" id="add-class-form" class="grid gap-5 sm:grid-cols-2">
            @csrf
            <x-modal.form-name name="add-class" />
            <x-form.input name="name" label="Class name" placeholder="e.g. 10" required />
            <x-form.input name="sort_order" type="number" min="0" label="Sort order" :value="($classes->max('sort_order') ?? 0) + 1" hint="Lower comes first." />
            <x-form.input name="sections" label="Sections" placeholder="e.g. A, B, C" required hint="Separate sections with commas." wrapper="sm:col-span-2" />
        </form>
        <x-slot:footer><x-button form="add-class-form" icon="plus">Add class</x-button></x-slot:footer>
    </x-modal>
</x-layouts.app>
