<x-layouts.app title="Hostel">
    <x-page-header title="Hostel" :breadcrumbs="['Dashboard' => route('home'), 'Hostel' => null]">
        <x-slot:actions>
            <x-button type="button" variant="secondary" icon="home-modern" x-data @click="$dispatch('open-modal', 'add-room')">Add room</x-button>
            <x-button type="button" icon="plus" x-data @click="$dispatch('open-modal', 'allocate')">Allocate a student</x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        <x-stat-card label="Beds" :value="$totals['beds']" icon="home-modern" />
        <x-stat-card label="Occupied" :value="$totals['used']" icon="users" color="amber" />
        <x-stat-card label="Free" :value="$totals['beds'] - $totals['used']" icon="check-circle" color="green" />
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
                            @if ($used < $room->capacity)
                                <x-icon-link icon="plus" label="Allocate a student here" tone="primary" x-data
                                             @click="$dispatch('open-modal', { name: 'allocate', fill: { hostel_room_id: {{ $room->id }} } })" />
                            @endif
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
        <x-empty-state icon="home-modern">No hostel rooms yet. Use Add room to create one.</x-empty-state>
    @endforelse

    <x-modal name="allocate" title="Allocate a student" description="Give a student a bed in a room with space." icon="users">
        <form method="POST" action="{{ route('hostel.allocations.store') }}" id="allocate-form" class="grid gap-5">
            @csrf
            <x-modal.form-name name="allocate" />
            <x-form.select name="student_id" label="Student" :options="$students" placeholder="Select student" required icon="user" />
            <x-form.select name="hostel_room_id" label="Room" :options="$roomOptions" placeholder="Select room" required icon="home-modern"
                           :hint="$roomOptions->isEmpty() ? 'No free beds. Add a room first.' : 'Only rooms with a free bed are listed.'" />
        </form>
        <x-slot:footer><x-button form="allocate-form" icon="plus">Allocate</x-button></x-slot:footer>
    </x-modal>

    <x-modal name="add-room" title="Add room" description="A room, its number of beds and the termly fee." icon="home-modern">
        <form method="POST" action="{{ route('hostel.rooms.store') }}" id="add-room-form" class="grid gap-5 sm:grid-cols-2">
            @csrf
            <x-modal.form-name name="add-room" />
            <x-form.input name="hostel" label="Hostel" placeholder="e.g. Boys Hostel" required list="hostel-names" hint="Rooms are grouped by hostel." />
            <x-form.input name="room_number" label="Room number" required placeholder="e.g. B-12" />
            <x-form.input name="capacity" type="number" min="1" max="50" label="Beds" required />
            <x-form.input name="fee" type="number" step="0.01" min="0" :label="'Fee per term ('.setting('currency_symbol').')'" />
            <datalist id="hostel-names">@foreach ($rooms->keys() as $hostel)<option value="{{ $hostel }}">@endforeach</datalist>
        </form>
        <x-slot:footer><x-button form="add-room-form" icon="plus">Add room</x-button></x-slot:footer>
    </x-modal>
</x-layouts.app>
