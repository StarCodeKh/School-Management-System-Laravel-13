<x-layouts.app title="Departments">
    <x-page-header title="Departments" :breadcrumbs="['Dashboard' => route('home'), 'Departments' => null]">
        <x-slot:actions>
            <x-button :href="route('department/add/page')" icon="plus">Add department</x-button>
        </x-slot:actions>
    </x-page-header>

    <x-search-bar :value="$search" placeholder="Search name, ID or head..." />

    <x-table>
        <x-slot:head>
            <tr><th>ID</th><th>Name</th><th>Head of department</th><th>Teachers</th><th>Started</th><th>Students</th><th class="text-right">Actions</th></tr>
        </x-slot:head>
        @forelse ($departments as $department)
            <tr class="hover:bg-slate-50">
                <td class="font-medium text-slate-900">{{ $department->code }}</td>
                <td class="font-medium">{{ $department->department_name }}</td>
                <td>{{ $department->head?->name ?? '-' }}</td>
                <td>{{ $department->teachers_count }}</td>
                <td>{{ $department->department_start_date?->format('d M Y') }}</td>
                <td>{{ number_format($department->no_of_students) }}</td>
                <td class="text-right">
                    <x-icon-link :href="url('department/edit/'.$department->code)" icon="pencil" label="Edit" tone="primary" />
                    <x-delete-button :action="route('department/delete')" :fields="['department_id' => $department->id]" title="Delete department"
                                     :message="'Delete '.$department->department_name.'? Its teachers will have no department.'" />
                </td>
            </tr>
        @empty
            <x-empty-row colspan="7" message="No departments found." />
        @endforelse
        <x-slot:footer>{{ $departments->links() }}</x-slot:footer>
    </x-table>
</x-layouts.app>
