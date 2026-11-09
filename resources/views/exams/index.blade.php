<x-layouts.app title="Exams">
    <x-page-header title="Exams" :breadcrumbs="['Dashboard' => route('home'), 'Exams' => null]">
        <x-slot:actions>
            @can('manage-academics')
                <x-button :href="route('exams.create')" icon="plus">New exam</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <form method="GET" class="mb-4 flex gap-2">
        <x-form.select name="class" :options="$classes->mapWithKeys(fn ($class) => [$class->id => 'Class '.$class->name])" :value="$classId"
                       placeholder="All classes" icon="academic-cap" wrapper="w-full sm:w-56" onchange="this.form.submit()" />
    </form>

    <x-table>
        <x-slot:head>
            <tr><th>Exam</th><th>Class</th><th>Term</th><th>Dates</th><th>Max marks</th><th>Marks entered</th><th class="text-right">Actions</th></tr>
        </x-slot:head>
        @forelse ($exams as $exam)
            <tr class="hover:bg-slate-50">
                <td><a href="{{ route('exams.show', $exam) }}" class="font-semibold text-primary-600 hover:text-primary-700">{{ $exam->name }}</a></td>
                <td><x-badge color="indigo">Class {{ $exam->schoolClass?->name }}</x-badge></td>
                <td>{{ $exam->term ?: '-' }}</td>
                <td>{{ $exam->start_date->format('d M Y') }}@if ($exam->end_date) &ndash; {{ $exam->end_date->format('d M Y') }}@endif</td>
                <td>{{ $exam->max_marks }}</td>
                <td>{{ $exam->results_count }}</td>
                <td class="text-right">
                    <x-icon-link :href="route('exams.marks', $exam)" icon="pencil" label="Enter marks" tone="primary" />
                    <x-icon-link :href="route('exams.show', $exam)" icon="eye" label="Results" />
                    @can('manage-academics')
                        <x-icon-link :href="route('exams.edit', $exam)" icon="cog" label="Edit exam" />
                        <x-delete-button :action="route('exams.destroy', $exam)" :fields="['_method' => 'DELETE']" title="Delete exam"
                                         :message="'Delete '.$exam->name.' and all its marks?'" />
                    @endcan
                </td>
            </tr>
        @empty
            <x-empty-row colspan="7" message="No exams yet." />
        @endforelse
        <x-slot:footer>{{ $exams->links() }}</x-slot:footer>
    </x-table>
</x-layouts.app>
