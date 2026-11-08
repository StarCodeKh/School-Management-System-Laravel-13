<x-layouts.app title="Teacher Dashboard">
    <x-page-header :title="'Welcome, '.auth()->user()->name.'!'" :breadcrumbs="['Home' => route('home'), 'Teacher Dashboard' => null]" />

    @unless ($teacher)
        <div class="mb-6 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
            <x-icon name="information" class="mt-0.5" />
            <p>Your account has no teacher profile yet, so personal details are not shown. An administrator can add one under <strong>Teachers &rarr; Teacher Add</strong>.</p>
        </div>
    @endunless

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat-card label="Students" :value="number_format($stats['students'])" icon="academic-cap" />
        <x-stat-card label="Classes" :value="number_format($stats['classes'])" icon="squares" color="sky" />
        <x-stat-card label="Subjects" :value="number_format($stats['subjects'])" icon="book" color="amber" />
        <x-stat-card label="Department colleagues" :value="number_format($stats['colleagues'])" icon="users" color="green" />
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        <x-card title="My profile">
            @if ($teacher)
                <div class="flex items-center gap-3">
                    <x-avatar :src="auth()->user()->avatar_url" :alt="$teacher->full_name" size="size-14" />
                    <div>
                        <p class="font-semibold text-slate-900">{{ $teacher->full_name }}</p>
                        <p class="text-sm text-slate-500">{{ $teacher->qualification }}</p>
                    </div>
                </div>
                <dl class="mt-5 space-y-3 text-sm">
                    @foreach ([
                        'Department' => $teacher->department?->department_name,
                        'Head of department' => $teacher->department?->head?->name,
                        'Experience' => $teacher->experience.' years',
                        'Phone' => $teacher->phone_number,
                        'City' => $teacher->city,
                    ] as $label => $value)
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500">{{ $label }}</dt>
                            <dd class="text-right font-medium text-slate-900">{{ $value ?: '-' }}</dd>
                        </div>
                    @endforeach
                </dl>
            @else
                <p class="text-sm text-slate-500">No teacher profile linked to this account.</p>
            @endif
        </x-card>

        <x-card title="Classes" :padding="false" class="xl:col-span-2">
            <x-table class="rounded-none border-0 shadow-none">
                <x-slot:head>
                    <tr><th>Class</th><th>Sections</th><th>Students</th><th>Subjects</th></tr>
                </x-slot:head>
                @forelse ($classes as $class)
                    <tr>
                        <td class="font-medium text-slate-900">Class {{ $class->name }}</td>
                        <td>{{ $class->sections->pluck('name')->implode(', ') ?: '-' }}</td>
                        <td>{{ $class->students_count }}</td>
                        <td>{{ $class->subjects_count }}</td>
                    </tr>
                @empty
                    <x-empty-row colspan="4" message="No classes yet." />
                @endforelse
            </x-table>
        </x-card>
    </div>

    @if ($teacher?->department || $headedDepartments->isNotEmpty())
        <div class="mt-6 grid gap-6 xl:grid-cols-2">
            @if ($teacher?->department)
                <x-card :title="$teacher->department->department_name.' colleagues'" :padding="false">
                    <ul class="divide-y divide-slate-100">
                        @forelse ($teacher->department->teachers->where('id', '!=', $teacher->id) as $colleague)
                            <li class="flex items-center gap-3 px-5 py-3 text-sm">
                                <x-avatar :src="$colleague->user?->avatar_url" :alt="$colleague->full_name" size="size-8" />
                                <span class="flex-1 font-medium text-slate-900">{{ $colleague->full_name }}</span>
                                <span class="text-slate-500">{{ $colleague->qualification }}</span>
                            </li>
                        @empty
                            <li class="px-5 py-6 text-center text-sm text-slate-500">You are the only teacher in this department.</li>
                        @endforelse
                    </ul>
                </x-card>
            @endif

            @if ($headedDepartments->isNotEmpty())
                <x-card title="Departments you lead" :padding="false">
                    <ul class="divide-y divide-slate-100">
                        @foreach ($headedDepartments as $department)
                            <li class="flex items-center justify-between px-5 py-3 text-sm">
                                <span class="font-medium text-slate-900">{{ $department->department_name }}</span>
                                <x-badge>{{ $department->teachers_count }} teachers</x-badge>
                            </li>
                        @endforeach
                    </ul>
                </x-card>
            @endif
        </div>
    @endif
</x-layouts.app>
