@php
    $canEdit = $section && auth()->user()->can('manage-academics') && $subjects->isNotEmpty();
    // the usual times of each period, so a new lesson starts with the right ones
    $times = collect($grid)->map(fn ($row) => [substr($row->first()->start_time, 0, 5), substr($row->first()->end_time, 0, 5)]);
@endphp

<x-layouts.app title="Timetable">
    <x-page-header title="Timetable" :breadcrumbs="['Dashboard' => route('home'), 'Timetable' => null]">
        @if ($canEdit)
            <x-slot:actions>
                <x-button type="button" icon="plus" x-data @click="$dispatch('open-modal', 'period')">Add period</x-button>
            </x-slot:actions>
        @endif
    </x-page-header>

    <x-card class="mb-6">
        <form method="GET" class="grid gap-4 sm:grid-cols-[1fr_auto] sm:items-end">
            <x-form.select name="section_id" label="Section" :options="$sectionOptions" :value="$section?->id" placeholder="Select a section" onchange="this.form.submit()" />
            <x-button variant="secondary">Show</x-button>
        </form>
    </x-card>

    @if ($section)
        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full table-fixed text-sm">
                <thead class="bg-slate-50 text-xs font-semibold tracking-wide text-slate-500 uppercase">
                    <tr>
                        <th class="w-20 px-3 py-3 text-left">Period</th>
                        @foreach ($days as $day)<th class="min-w-36 px-3 py-3 text-left">{{ $day->label() }}</th>@endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($periods as $period)
                        <tr>
                            <td class="px-3 py-3 font-semibold text-slate-900">{{ $period }}</td>
                            @foreach ($days as $day)
                                @php($entry = $grid[$period][$day->value] ?? null)
                                <td class="px-2 py-2 align-top">
                                    @if ($entry)
                                        <div class="group relative rounded-lg border border-primary-100 bg-primary-50 p-2">
                                            <p class="font-semibold text-primary-700">{{ $entry->subject?->subject_name }}</p>
                                            <p class="text-xs text-slate-600">{{ $entry->timeRange() }}</p>
                                            <p class="text-xs text-slate-500">{{ $entry->teacher?->full_name ?? 'No teacher' }}@if ($entry->room) &middot; {{ $entry->room }}@endif</p>
                                            @if ($canEdit)
                                                <div class="absolute top-1 right-1 flex rounded-lg bg-white/90 opacity-0 shadow-sm ring-1 ring-slate-200 transition group-hover:opacity-100 group-focus-within:opacity-100">
                                                    <x-icon-link icon="pencil" label="Change this lesson" tone="primary" x-data
                                                                 @click="$dispatch('open-modal', { name: 'period', fill: {{ \Illuminate\Support\Js::from([
                                                                     'day_of_week' => $day->value, 'period' => $period,
                                                                     'start_time' => substr($entry->start_time, 0, 5), 'end_time' => substr($entry->end_time, 0, 5),
                                                                     'subject_id' => $entry->subject_id, 'teacher_id' => $entry->teacher_id, 'room' => $entry->room,
                                                                 ]) }} })" />
                                                    <x-delete-button :action="route('timetable.destroy', $entry)" :fields="['_method' => 'DELETE']" title="Remove period"
                                                                     :message="'Remove '.$entry->subject?->subject_name.' on '.$day->label().', period '.$period.'?'" />
                                                </div>
                                            @endif
                                        </div>
                                    @elseif ($canEdit)
                                        <button type="button" x-data
                                                @click="$dispatch('open-modal', { name: 'period', fill: {{ \Illuminate\Support\Js::from([
                                                    'day_of_week' => $day->value, 'period' => $period, 'start_time' => $times[$period][0] ?? '', 'end_time' => $times[$period][1] ?? '',
                                                    'subject_id' => '', 'teacher_id' => '', 'room' => '',
                                                ]) }} })"
                                                class="group grid min-h-16 w-full place-items-center rounded-lg border border-dashed border-transparent text-slate-300 transition hover:border-primary-300 hover:bg-primary-50/50 hover:text-primary-500 focus-visible:border-primary-400 focus-visible:outline-none"
                                                aria-label="Add a lesson on {{ $day->label() }}, period {{ $period }}">
                                            <x-icon name="plus" class="size-4 opacity-0 transition group-hover:opacity-100 group-focus-visible:opacity-100" />
                                        </button>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($section && auth()->user()->can('manage-academics') && $subjects->isEmpty())
            <x-alert type="info" class="mt-6">Class {{ $section->schoolClass?->name }} has no subjects yet. <a href="{{ route('subject/add/page') }}" class="font-medium underline">Add subjects</a> to build its timetable.</x-alert>
        @endif

        @if ($canEdit)
            <x-modal name="period" :title="'Lesson for '.$section->label()" description="Saving a day and period that already has a lesson replaces it." icon="calendar" size="lg">
                <form method="POST" action="{{ route('timetable.store') }}" id="period-form" class="grid gap-5 sm:grid-cols-2">
                    @csrf
                    <x-modal.form-name name="period" />
                    <input type="hidden" name="section_id" value="{{ $section->id }}">
                    <x-form.select name="day_of_week" label="Day" :options="\App\Enums\Weekday::options()" placeholder="Select day" required icon="calendar" />
                    <x-form.input name="period" type="number" min="1" max="12" label="Period" :value="1" required icon="hashtag" />
                    <x-form.input name="start_time" type="time" label="Starts" value="08:00" required icon="clock" />
                    <x-form.input name="end_time" type="time" label="Ends" value="08:45" required icon="clock" />
                    <x-form.select name="subject_id" label="Subject" :options="$subjects" placeholder="Select subject" required icon="book" />
                    <x-form.select name="teacher_id" label="Teacher" :options="$teachers" placeholder="-- None --" icon="user" hint="A teacher cannot be in two sections in the same period." />
                    <x-form.input name="room" label="Room" placeholder="e.g. B-204" wrapper="sm:col-span-2" />
                </form>
                <x-slot:footer><x-button form="period-form" icon="check">Save lesson</x-button></x-slot:footer>
            </x-modal>
        @endif
    @else
        <x-empty-state icon="calendar">Choose a section to see its weekly timetable.</x-empty-state>
    @endif
</x-layouts.app>
