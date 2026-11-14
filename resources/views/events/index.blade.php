<x-layouts.app title="Events">
    <x-page-header title="Events" :breadcrumbs="['Dashboard' => route('home'), 'Events' => null]">
        <x-slot:actions>
            @can('manage-communication')
                <x-button :href="route('events.create')" icon="plus">New event</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="grid gap-6 xl:grid-cols-[1fr_20rem]">
        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-3">
                <x-icon-link :href="route('events.index', ['month' => $month->copy()->subMonthNoOverflow()->format('Y-m')])" icon="arrow-left" label="Previous month" />
                <h2 class="text-base font-semibold text-slate-900">{{ $month->format('F Y') }}</h2>
                <x-icon-link :href="route('events.index', ['month' => $month->copy()->addMonthNoOverflow()->format('Y-m')])" icon="chevron-right" label="Next month" />
            </div>
            <div class="grid grid-cols-7 border-b border-slate-200 bg-slate-50 text-center text-xs font-semibold text-slate-500 uppercase">
                @foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $label)<div class="py-2">{{ $label }}</div>@endforeach
            </div>
            <div class="grid grid-cols-7">
                @foreach ($days as $date => $dayEvents)
                    @php($day = \Illuminate\Support\Carbon::parse($date))
                    <div @class(['min-h-14 border-r border-b border-slate-100 p-1 text-xs sm:min-h-24 sm:p-1.5', 'bg-slate-50/60 text-slate-400' => ! $day->isSameMonth($month)])>
                        <span @class(['inline-grid size-6 place-items-center rounded-full', 'bg-primary-600 font-semibold text-white' => $day->isToday()])>{{ $day->day }}</span>
                        {{-- phones: a dot per event (titles are in the Upcoming list); wider screens: the titles --}}
                        @if ($dayEvents->isNotEmpty())
                            <span class="mt-1 flex flex-wrap gap-0.5 px-1 sm:hidden" title="{{ $dayEvents->pluck('title')->implode(', ') }}">
                                @foreach ($dayEvents as $event)<span class="size-1.5 rounded-full bg-primary-500"></span>@endforeach
                            </span>
                        @endif
                        @foreach ($dayEvents as $event)
                            <p class="mt-1 hidden truncate rounded bg-primary-50 px-1.5 py-0.5 font-medium text-primary-700 sm:block" title="{{ $event->title }}">{{ $event->title }}</p>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </section>

        <x-card title="Upcoming" :padding="false">
            <ul class="divide-y divide-slate-100">
                @forelse ($upcoming as $event)
                    <li class="px-5 py-3">
                        <div class="flex items-start justify-between gap-2">
                            <p class="font-medium text-slate-900">{{ $event->title }}</p>
                            @can('manage-communication')
                                <div class="flex shrink-0">
                                    <x-icon-link :href="route('events.edit', $event)" icon="pencil" label="Edit" tone="primary" />
                                    <x-delete-button :action="route('events.destroy', $event)" :fields="['_method' => 'DELETE']" title="Delete event" :message="'Delete '.$event->title.'?'" />
                                </div>
                            @endcan
                        </div>
                        <p class="text-xs text-slate-500">{{ $event->dateRange() }}@if ($event->location) &middot; {{ $event->location }}@endif</p>
                        @if ($event->description)<p class="mt-1 text-sm text-slate-600">{{ \Illuminate\Support\Str::limit($event->description, 120) }}</p>@endif
                        <x-badge :color="$event->audience->color()" class="mt-1.5">{{ $event->audience->label() }}</x-badge>
                    </li>
                @empty
                    <li class="px-5 py-8 text-center text-sm text-slate-500">No upcoming events.</li>
                @endforelse
            </ul>
        </x-card>
    </div>
</x-layouts.app>
