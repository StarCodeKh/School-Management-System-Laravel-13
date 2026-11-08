<x-layouts.app title="Students">
    <x-page-header title="Students" :breadcrumbs="['Dashboard' => route('home'), 'Students' => null]">
        <x-slot:actions>
@can('manage-school')
            <x-button :href="route('student/add/page')" icon="plus">Add student</x-button>
@endcan
        </x-slot:actions>
    </x-page-header>

    @include('student._toolbar')

    <x-table>
        <x-slot:head>
            <tr><th>Admission ID</th><th>Name</th><th>Class</th><th>Date of birth</th><th>Phone</th><th class="text-right">Actions</th></tr>
        </x-slot:head>
        @forelse ($studentList as $student)
            <tr class="hover:bg-slate-50">
                <td class="font-medium text-slate-900">{{ $student->admission_id }}</td>
                <td>
                    <a href="{{ url('student/profile/'.$student->id) }}" class="flex items-center gap-3 hover:text-primary-600">
                        <x-avatar :src="$student->photo_url" :alt="$student->full_name" size="size-8" />
                        <span class="font-medium">{{ $student->full_name }}</span>
                    </a>
                </td>
                <td>{{ $student->schoolClass?->name }} - {{ $student->section?->name }}</td>
                <td>{{ $student->date_of_birth?->format('d M Y') }}</td>
                <td>{{ $student->phone_number }}</td>
                <td class="text-right">
                    <x-icon-link :href="url('student/profile/'.$student->id)" icon="eye" label="View" />
@can('manage-school')
                    <x-icon-link :href="url('student/edit/'.$student->id)" icon="pencil" label="Edit" tone="primary" />
                    <x-delete-button :action="route('student/delete')" :fields="['id' => $student->id]" title="Delete student"
                                     :message="'Delete '.$student->full_name.'? This cannot be undone.'" />
@endcan
                </td>
            </tr>
        @empty
            <x-empty-row colspan="6" message="No students found." />
        @endforelse
        <x-slot:footer>{{ $studentList->links() }}</x-slot:footer>
    </x-table>
</x-layouts.app>
