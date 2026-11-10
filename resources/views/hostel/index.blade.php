<x-layouts.app title="Hostel">
    <x-page-header title="Hostel" :breadcrumbs="['Dashboard' => route('home'), 'Hostel' => null]" />

    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        <x-stat-card label="Beds" :value="$totals['beds']" icon="home-modern" />
        <x-stat-card label="Occupied" :value="$totals['used']" icon="users" color="amber" />
        <x-stat-card label="Free" :value="$totals['beds'] - $totals['used']" icon="check-circle" color="green" />
    </div>

    <div class="mb-6 grid gap-6 xl:grid-cols-2">
        <x-card title="Allocate a student">
            <form method="POST" action="{{ route('hostel.allocations.store') }}" class="grid gap-5 sm:grid-cols-2">
                @csrf
                <x-form.select name="student_id" label="Student" :options="$students" placeholder="Select student" required />
                <x-form.select name="hostel_room_id" label="Room" :options="$roomOptions" placeholder="Select room" required
                               :hint="$roomOptions->isEmpty() ? 'No free beds. Add a room first.' : null" />
                <div class="sm:col-span-2 flex justify-end"><x-button icon="plus">Allocate</x-button></div>
            </form>
        </x-card>

        <x-card title="Add room">
            <form method="POST" action="{{ route('hostel.rooms.store') }}" class="grid gap-5 sm:grid-cols-2">
                @csrf
                <x-form.input name="hostel" label="Hostel" placeholder="e.g. Boys Hostel" required />
                <x-form.input name="room_number" label="Room number" required />
                <x-form.input name="capacity" type="number" min="1" max="50" label="Beds" required />
                <x-form.input name="fee" type="number" step="0.01" min="0" :label="'Fee per term ('.setting('currency_symbol').')'" />
                <div class="sm:col-span-2 flex justify-end"><x-button icon="plus">Add room</x-button></div>
            </form>
        </x-card>
    </div>

    @forelse ($rooms as $hostel => $hostelRooms)
        <h2 class="mb-3 text-lg font-semibold text-slate-900">{{ $hostel }}</h2>
        <div class="mb-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($hostelRooms as $room)
                @php($used = $room->allocations->count())
                <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                        <div>
                            <p class="font-semibold text-slate-900">Room {{ $room->room_number }}</p>
                            <p class="text-xs text-slate-500">{{ money($room->fee) }} per term</p>
                        </div>
                        <div class="flex items-center gap-1">
                            <x-badge :color="$used >= $room->capacity ? 'red' : 'green'">{{ $used }} / {{ $room->capacity }}</x-badge>
                            @if ($used === 0)
                                <x-delete-button :action="route('hostel.rooms.destroy', $room)" :fields="['_method' => 'DELETE']" title="Delete room" :message="'Delete '.$room->label().'?'" />
                            @endif
                        </div>
                    </div>
                    <ul class="divide-y divide-slate-100">
                        @forelse ($room->allocations as $allocation)
                            <li class="flex items-center justify-between gap-2 px-4 py-2 text-sm">
                                <span>{{ $allocation->student?->full_name }}</span>
                                <x-delete-button :action="route('hostel.allocations.destroy', $allocation)" :fields="['_method' => 'DELETE']" title="Move out"
                                                 :message="'Move '.$allocation->student?->full_name.' out of '.$room->label().'?'" />
                            </li>
                        @empty
                            <li class="px-4 py-4 text-center text-sm text-slate-400">Empty</li>
                        @endforelse
                    </ul>
                </section>
            @endforeach
        </div>
    @empty
        <x-empty-state icon="home-modern">No hostel rooms yet.</x-empty-state>
    @endforelse
</x-layouts.app>
