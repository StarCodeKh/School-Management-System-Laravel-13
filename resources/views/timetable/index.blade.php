<x-layouts.app title="Timetable">
    <x-page-header title="Timetable" :breadcrumbs="['Dashboard' => route('home'), 'Timetable' => null]" />

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
                                            @can('manage-academics')
                                                <div class="absolute top-1 right-1 hidden group-hover:block">
                                                    <x-delete-button :action="route('timetable.destroy', $entry)" :fields="['_method' => 'DELETE']" title="Remove period"
                                                                     :message="'Remove '.$entry->subject?->subject_name.' on '.$day->label().', period '.$period.'?'" />
                                                </div>
                                            @endcan
                                        </div>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @can('manage-academics')
            <x-card title="Add or replace a period" class="mt-6">
                @if ($subjects->isEmpty())
                    <p class="text-sm text-slate-500">Class {{ $section->schoolClass?->name }} has no subjects yet. <a href="{{ route('subject/add/page') }}" class="text-primary-600 hover:underline">Add subjects</a> first.</p>
                @else
                    <form method="POST" action="{{ route('timetable.store') }}" class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                        @csrf
                        <input type="hidden" name="section_id" value="{{ $section->id }}">
                        <x-form.select name="day_of_week" label="Day" :options="\App\Enums\Weekday::options()" placeholder="Select day" required />
                        <x-form.input name="period" type="number" min="1" max="12" label="Period" :value="1" required />
                        <x-form.input name="start_time" type="time" label="Starts" value="08:00" required />
                        <x-form.input name="end_time" type="time" label="Ends" value="08:45" required />
                        <x-form.select name="subject_id" label="Subject" :options="$subjects" placeholder="Select subject" required />
                        <x-form.select name="teacher_id" label="Teacher" :options="$teachers" placeholder="-- None --" />
                        <x-form.input name="room" label="Room" placeholder="e.g. B-204" />
                        <div class="flex items-end"><x-button class="w-full" icon="plus">Save period</x-button></div>
                    </form>
                    <p class="mt-3 text-xs text-slate-500">Saving a day and period that already exist replaces it. A teacher cannot be booked in two sections in the same period.</p>
                @endif
            </x-card>
        @endcan
    @else
        <p class="rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center text-sm text-slate-500">Choose a section to see its weekly timetable.</p>
    @endif
</x-layouts.app>
