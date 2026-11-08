<x-layouts.app title="Subjects">
    <x-page-header title="Subjects" :breadcrumbs="['Dashboard' => route('home'), 'Subjects' => null]">
        <x-slot:actions>
@can('manage-school')
            <x-button :href="route('subject/add/page')" icon="plus">Add subject</x-button>
@endcan
        </x-slot:actions>
    </x-page-header>

    <x-search-bar :value="$search" placeholder="Search name or ID...">
        <select name="class" class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500">
            <option value="">All classes</option>
            @foreach ($classes as $class)
                <option value="{{ $class->id }}" @selected($classId === $class->id)>Class {{ $class->name }}</option>
            @endforeach
        </select>
    </x-search-bar>

    <x-table>
        <x-slot:head>
            <tr><th>ID</th><th>Name</th><th>Class</th><th class="text-right">Actions</th></tr>
        </x-slot:head>
        @forelse ($subjectList as $subject)
            <tr class="hover:bg-slate-50">
                <td class="font-medium text-slate-900">{{ $subject->code }}</td>
                <td class="font-medium">{{ $subject->subject_name }}</td>
                <td><x-badge color="indigo">Class {{ $subject->schoolClass?->name }}</x-badge></td>
                <td class="text-right">
@can('manage-school')
                    <x-icon-link :href="url('subject/edit/'.$subject->code)" icon="pencil" label="Edit" tone="primary" />
                    <x-delete-button :action="route('subject/delete')" :fields="['code' => $subject->code]" title="Delete subject"
                                     :message="'Delete '.$subject->subject_name.'?'" />
@endcan
                </td>
            </tr>
        @empty
            <x-empty-row colspan="4" message="No subjects found." />
        @endforelse
        <x-slot:footer>{{ $subjectList->links() }}</x-slot:footer>
    </x-table>
</x-layouts.app>
