<x-layouts.app title="Teachers">
    <x-page-header title="Teachers" :breadcrumbs="['Dashboard' => route('home'), 'Teachers' => null]">
        <x-slot:actions>
            <x-button :href="route('teacher/add/page')" icon="plus">Add teacher</x-button>
        </x-slot:actions>
    </x-page-header>

    @include('teacher._toolbar')

    <x-table>
        <x-slot:head>
            <tr><th>User ID</th><th>Name</th><th>Department</th><th>Qualification</th><th>Experience</th><th>Phone</th><th class="text-right">Actions</th></tr>
        </x-slot:head>
        @forelse ($teachers as $teacher)
            <tr class="hover:bg-slate-50">
                <td class="font-medium text-slate-900">{{ $teacher->user?->code }}</td>
                <td>
                    <div class="flex items-center gap-3">
                        <x-avatar :src="$teacher->user?->avatar_url" :alt="$teacher->full_name" size="size-8" />
                        <div>
                            <p class="font-medium text-slate-900">{{ $teacher->full_name }}</p>
                            <p class="text-xs text-slate-500">{{ $teacher->gender?->label() }}</p>
                        </div>
                    </div>
                </td>
                <td>{{ $teacher->department?->department_name ?? '-' }}</td>
                <td>{{ $teacher->qualification }}</td>
                <td>{{ $teacher->experience }} yrs</td>
                <td>{{ $teacher->phone_number }}</td>
                <td class="text-right">
                    <x-icon-link :href="url('teacher/edit/'.$teacher->id)" icon="pencil" label="Edit" tone="primary" />
                    <x-delete-button :action="route('teacher/delete')" :fields="['id' => $teacher->id]" title="Delete teacher"
                                     :message="'Delete the teacher profile of '.$teacher->full_name.'? The user account is kept.'" />
                </td>
            </tr>
        @empty
            <x-empty-row colspan="7" message="No teachers found." />
        @endforelse
        <x-slot:footer>{{ $teachers->links() }}</x-slot:footer>
    </x-table>
</x-layouts.app>
