<x-layouts.app :title="$route->name">
    <x-page-header :title="$route->name" :breadcrumbs="['Transport' => route('transport.index'), $route->name => null]">
        <x-slot:actions>
            <x-button type="button" icon="plus" x-data @click="$dispatch('open-modal', 'add-rider')">Add a student</x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="grid gap-6 xl:grid-cols-3">
        <x-card title="Route details">
            <form method="POST" action="{{ route('transport.routes.update', $route) }}" class="space-y-4">
                @csrf
                @method('PUT')
                <x-form.input name="name" label="Name" :value="$route->name" required />
                <x-form.select name="vehicle_id" label="Vehicle" :options="\App\Models\Vehicle::orderBy('registration_number')->pluck('registration_number', 'id')" :value="$route->vehicle_id" placeholder="-- None --" />
                <x-form.input name="fare" type="number" step="0.01" min="0" label="Fare per term" :value="$route->fare" />
                <x-form.textarea name="stops" label="Stops" rows="5" :value="$route->stops" hint="One stop per line." />
                <x-button class="w-full">Save route</x-button>
            </form>
            @if ($route->vehicle)
                <p class="mt-4 rounded-lg bg-slate-50 p-3 text-sm text-slate-600">
                    {{ $route->vehicle->registration_number }} &middot; {{ $route->vehicle->capacity }} seats<br>
                    Driver: {{ $route->vehicle->driver_name ?: '-' }} {{ $route->vehicle->driver_phone }}
                </p>
            @endif
        </x-card>

        <div class="space-y-6 xl:col-span-2">
            <x-table>
                <x-slot:head>
                    <tr><th>Student</th><th>Class</th><th>Pickup stop</th><th class="text-right">Actions</th></tr>
                </x-slot:head>
                @forelse ($route->assignments as $assignment)
                    <tr>
                        <td class="font-medium text-slate-900">{{ $assignment->student?->full_name }}</td>
                        <td>{{ $assignment->student?->schoolClass?->name }} - {{ $assignment->student?->section?->name }}</td>
                        <td>{{ $assignment->pickup_stop ?: '-' }}</td>
                        <td class="text-right">
                            <x-delete-button :action="route('transport.assignments.destroy', $assignment)" :fields="['_method' => 'DELETE']" title="Remove rider"
                                             :message="'Remove '.$assignment->student?->full_name.' from this route?'" />
                        </td>
                    </tr>
                @empty
                    <x-empty-row colspan="4" message="No students on this route." />
                @endforelse
            </x-table>

        </div>
    </div>

    <x-modal name="add-rider" title="Add a student" :description="'Put a student on '.$route->name.'.'" icon="users" size="sm">
        <form method="POST" action="{{ route('transport.routes.assign', $route) }}" id="add-rider-form" class="grid gap-5">
            @csrf
            <x-modal.form-name name="add-rider" />
            <x-form.select name="student_id" label="Student" :options="$students" placeholder="Select student" required icon="user"
                           :hint="$students->isEmpty() ? 'Every student already has a route.' : 'Students already on a route are not listed.'" />
            <x-form.select name="pickup_stop" label="Pickup stop" :options="array_combine($route->stopList(), $route->stopList())" placeholder="-- Not set --" icon="map" />
        </form>
        <x-slot:footer><x-button form="add-rider-form" icon="plus">Add</x-button></x-slot:footer>
    </x-modal>
</x-layouts.app>
