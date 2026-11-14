<x-layouts.app title="Teacher Dashboard">
    <x-page-header :title="'Welcome, '.auth()->user()->name.'!'" :breadcrumbs="['Home' => route('home'), 'Teacher Dashboard' => null]" />

    @unless ($teacher)
        <x-alert type="warning" title="No teacher profile yet" class="mb-6">Your account has no teacher profile yet, so personal details are not shown. An administrator can add one under <strong>Teachers &rarr; Teacher Add</strong>.</x-alert>
    @endunless

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat-card label="Classes today" :value="$todayClasses->count()" icon="calendar" :href="route('timetable.index')" />
        <x-stat-card label="Students" :value="number_format($stats['students'])" icon="academic-cap" color="sky" />
        <x-stat-card label="Subjects" :value="number_format($stats['subjects'])" icon="book" color="amber" />
        <x-stat-card label="Department colleagues" :value="number_format($stats['colleagues'])" icon="users" color="green" />
    </div>

    @if ($teacher)
        <x-card :title="'Today\'s classes'.(\App\Enums\Weekday::today() ? ' ('.\App\Enums\Weekday::today()->label().')' : '')" :padding="false" class="mt-6">
            <x-slot:actions>
                <x-button variant="ghost" size="sm" :href="route('attendance.index')">Take attendance</x-button>
            </x-slot:actions>
            <ul class="divide-y divide-slate-100">
                @forelse ($todayClasses as $entry)
                    <li class="flex items-center gap-4 px-5 py-3 text-sm">
                        <span class="w-24 font-medium text-slate-500">{{ $entry->timeRange() }}</span>
                        <span class="flex-1 font-semibold text-slate-900">{{ $entry->subject?->subject_name }}</span>
                        <x-badge color="indigo">Section {{ $entry->section?->label() }}</x-badge>
                        <span class="hidden text-slate-500 sm:block">{{ $entry->room }}</span>
                        <x-button size="sm" variant="secondary" :href="route('attendance.index', ['section_id' => $entry->section_id])">Attendance</x-button>
                    </li>
                @empty
                    <li class="px-5 py-8 text-center text-sm text-slate-500">No classes scheduled for you today.</li>
                @endforelse
            </ul>
        </x-card>
    @endif

    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        @if ($teacher)
            <x-profile-summary :image="auth()->user()->avatar_url" :name="$teacher->full_name" :subtitle="$teacher->qualification"
                               :details="[
                                   'Department' => $teacher->department?->department_name,
                                   'Head of department' => $teacher->department?->head?->name,
                                   'Experience' => $teacher->experience.' years',
                                   'Phone' => $teacher->phone_number,
                                   'City' => $teacher->city,
                               ]">
                <x-slot:badges><x-badge color="indigo">Teacher</x-badge></x-slot:badges>
            </x-profile-summary>
        @else
            <x-card title="My profile"><p class="text-sm text-slate-500">No teacher profile linked to this account.</p></x-card>
        @endif

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
                            <li class="px-5 py-8 text-center text-sm text-slate-500">You are the only teacher in this department.</li>
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
    <div class="mt-6"><x-noticeboard /></div>
</x-layouts.app>
