<div class="grid gap-6 xl:grid-cols-2">
    <x-card title="Latest notices" :padding="false">
        <x-slot:actions><x-button variant="ghost" size="sm" :href="route('notices.index')">All notices</x-button></x-slot:actions>
        <ul class="divide-y divide-slate-100">
            @forelse ($notices as $notice)
                <li class="px-5 py-3">
                    <div class="flex items-center gap-2">
                        @if ($notice->is_pinned)<x-badge color="indigo">Pinned</x-badge>@endif
                        <p class="font-medium text-slate-900">{{ $notice->title }}</p>
                    </div>
                    <p class="mt-0.5 line-clamp-2 text-sm text-slate-600">{{ $notice->body }}</p>
                    <p class="mt-1 text-xs text-slate-400">{{ $notice->published_at->diffForHumans() }}</p>
                </li>
            @empty
                <li class="px-5 py-8 text-center text-sm text-slate-500">No notices right now.</li>
            @endforelse
        </ul>
    </x-card>

    <x-card title="Upcoming events" :padding="false">
        <x-slot:actions><x-button variant="ghost" size="sm" :href="route('events.index')">Calendar</x-button></x-slot:actions>
        <ul class="divide-y divide-slate-100">
            @forelse ($events as $event)
                <li class="flex items-center gap-4 px-5 py-3">
                    <span class="grid w-12 shrink-0 rounded-lg bg-primary-50 py-1 text-center text-primary-700">
                        <span class="text-xs font-semibold uppercase">{{ $event->starts_on->format('M') }}</span>
                        <span class="text-lg leading-none font-bold">{{ $event->starts_on->format('d') }}</span>
                    </span>
                    <div class="min-w-0">
                        <p class="truncate font-medium text-slate-900">{{ $event->title }}</p>
                        <p class="text-xs text-slate-500">{{ $event->dateRange() }}@if ($event->location) &middot; {{ $event->location }}@endif</p>
                    </div>
                </li>
            @empty
                <li class="px-5 py-8 text-center text-sm text-slate-500">No upcoming events.</li>
            @endforelse
        </ul>
    </x-card>
</div>
