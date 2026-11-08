<x-layouts.app title="Attendance">
    <x-page-header title="Attendance" :breadcrumbs="['Dashboard' => route('home'), 'Attendance' => null]">
        <x-slot:actions>
            <x-button variant="secondary" icon="clipboard" :href="route('attendance.report', ['section_id' => $section?->id])">Report</x-button>
        </x-slot:actions>
    </x-page-header>

    <x-card class="mb-6">
        <form method="GET" class="grid gap-4 sm:grid-cols-[1fr_12rem_auto] sm:items-end">
            <x-form.select name="section_id" label="Section" :options="$sectionOptions" :value="$section?->id" placeholder="Select a section" required />
            <x-form.input name="date" type="date" label="Date" :value="$date" :max="today()->toDateString()" required />
            <x-button>Open register</x-button>
        </form>
    </x-card>

    @if ($section)
        <form method="POST" action="{{ route('attendance.store') }}" x-data="{ all(status) { $root.querySelectorAll(`input[type=radio][value=${status}]`).forEach(el => el.checked = true) } }">
            @csrf
            <input type="hidden" name="section_id" value="{{ $section->id }}">
            <input type="hidden" name="date" value="{{ $date }}">

            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                <p class="text-sm text-slate-600">
                    <span class="font-semibold text-slate-900">Section {{ $section->label() }}</span>
                    &middot; {{ \Illuminate\Support\Carbon::parse($date)->format('l, d M Y') }}
                    &middot; {{ $records->count() }} of {{ $students->count() }} recorded
                </p>
                <div class="flex gap-2">
                    @foreach ($statuses as $status)
                        <x-button type="button" variant="secondary" size="sm" @click="all('{{ $status->value }}')">All {{ strtolower($status->label()) }}</x-button>
                    @endforeach
                </div>
            </div>

            <x-table>
                <x-slot:head>
                    <tr><th>Roll</th><th>Student</th><th>Status</th><th>Remark</th></tr>
                </x-slot:head>
                @forelse ($students as $student)
                    @php($current = old("attendance.{$student->id}.status", $records[$student->id]->status->value ?? 'present'))
                    <tr>
                        <td class="text-slate-500">{{ $student->roll }}</td>
                        <td>
                            <div class="flex items-center gap-3">
                                <x-avatar :src="$student->photo_url" :alt="$student->full_name" size="size-8" />
                                <span class="font-medium text-slate-900">{{ $student->full_name }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="flex flex-wrap gap-1">
                                @foreach ($statuses as $status)
                                    <label class="cursor-pointer">
                                        <input type="radio" class="peer sr-only" name="attendance[{{ $student->id }}][status]" value="{{ $status->value }}" @checked($current === $status->value)>
                                        <span @class([
                                            'inline-block rounded-md border px-2.5 py-1 text-xs font-medium text-slate-500 border-slate-200 peer-focus-visible:ring-2 peer-focus-visible:ring-primary-500',
                                            'peer-checked:border-emerald-300 peer-checked:bg-emerald-50 peer-checked:text-emerald-700' => $status->value === 'present',
                                            'peer-checked:border-red-300 peer-checked:bg-red-50 peer-checked:text-red-700' => $status->value === 'absent',
                                            'peer-checked:border-amber-300 peer-checked:bg-amber-50 peer-checked:text-amber-800' => $status->value === 'late',
                                            'peer-checked:border-sky-300 peer-checked:bg-sky-50 peer-checked:text-sky-700' => $status->value === 'excused',
                                        ])>{{ $status->label() }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </td>
                        <td class="min-w-48">
                            <input type="text" name="attendance[{{ $student->id }}][remark]" value="{{ old("attendance.{$student->id}.remark", $records[$student->id]->remark ?? '') }}"
                                   placeholder="Optional" class="block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        </td>
                    </tr>
                @empty
                    <x-empty-row colspan="4" message="No students in this section." />
                @endforelse
            </x-table>

            @if ($students->isNotEmpty())
                <div class="mt-4 flex justify-end"><x-button icon="check">Save attendance</x-button></div>
            @endif
        </form>
    @else
        <p class="rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center text-sm text-slate-500">Choose a section and date to take attendance.</p>
    @endif
</x-layouts.app>
