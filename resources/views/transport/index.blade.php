<x-layouts.app title="Transport">
    <x-page-header title="Transport" :breadcrumbs="['Dashboard' => route('home'), 'Transport' => null]">
        <x-slot:actions>
            <x-button type="button" variant="secondary" icon="truck" x-data @click="$dispatch('open-modal', 'add-vehicle')">Add vehicle</x-button>
            <x-button type="button" icon="plus" x-data @click="$dispatch('open-modal', 'add-route')">Add route</x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="grid gap-6 xl:grid-cols-2">
        <div class="space-y-6">
            <x-table>
                <x-slot:head>
                    <tr><th>Route</th><th>Vehicle</th><th class="text-center">Riders</th><th class="text-right">Fare</th><th class="text-right">Actions</th></tr>
                </x-slot:head>
                @forelse ($routes as $route)
                    <tr class="hover:bg-slate-50">
                        <td>
                            <a href="{{ route('transport.routes.show', $route) }}" class="font-semibold text-primary-600 hover:text-primary-700">{{ $route->name }}</a>
                            <p class="text-xs text-slate-500">{{ count($route->stopList()) }} stops</p>
                        </td>
                        <td>{{ $route->vehicle?->registration_number ?? '-' }}</td>
                        <td class="text-center">
                            {{ $route->assignments_count }}@if ($route->vehicle) <span class="text-slate-400">/ {{ $route->vehicle->capacity }}</span>@endif
                        </td>
                        <td class="text-right">{{ money($route->fare) }}</td>
                        <td class="text-right">
                            <x-icon-link :href="route('transport.routes.show', $route)" icon="eye" label="Open" tone="primary" />
                            <x-delete-button :action="route('transport.routes.destroy', $route)" :fields="['_method' => 'DELETE']" title="Delete route"
                                             :message="'Delete '.$route->name.'? Its riders are removed from it.'" />
                        </td>
                    </tr>
                @empty
                    <x-empty-row colspan="5" message="No routes yet. Use Add route to create one." />
                @endforelse
            </x-table>

        </div>

        <div class="space-y-6">
            <x-table>
                <x-slot:head>
                    <tr><th>Vehicle</th><th>Driver</th><th class="text-center">Seats</th><th class="text-right">Actions</th></tr>
                </x-slot:head>
                @forelse ($vehicles as $vehicle)
                    <tr>
                        <td>
                            <p class="font-medium text-slate-900">{{ $vehicle->registration_number }}</p>
                            <p class="text-xs text-slate-500">{{ $vehicle->model }}</p>
                        </td>
                        <td>
                            <p>{{ $vehicle->driver_name ?: '-' }}</p>
                            <p class="text-xs text-slate-500">{{ $vehicle->driver_phone }}</p>
                        </td>
                        <td class="text-center">{{ $vehicle->capacity }}</td>
                        <td class="text-right">
                            <x-delete-button :action="route('transport.vehicles.destroy', $vehicle)" :fields="['_method' => 'DELETE']" title="Remove vehicle"
                                             :message="'Remove '.$vehicle->registration_number.'? Its routes keep running without a vehicle.'" />
                        </td>
                    </tr>
                @empty
                    <x-empty-row colspan="4" message="No vehicles yet. Use Add vehicle to register one." />
                @endforelse
            </x-table>

        </div>
    </div>

    <x-modal name="add-route" title="Add route" description="A bus route with its stops and termly fare." icon="map">
        <form method="POST" action="{{ route('transport.routes.store') }}" id="add-route-form" class="grid gap-5 sm:grid-cols-2">
            @csrf
            <x-modal.form-name name="add-route" />
            <x-form.input name="name" label="Route name" placeholder="e.g. Route 1 - City Centre" required wrapper="sm:col-span-2" />
            <x-form.select name="vehicle_id" label="Vehicle" :options="$vehicles->pluck('registration_number', 'id')" placeholder="-- None --" icon="truck" />
            <x-form.input name="fare" type="number" step="0.01" min="0" :label="'Fare per term ('.setting('currency_symbol').')'" />
            <x-form.textarea name="stops" label="Stops" rows="4" placeholder="One stop per line" hint="Riders choose their pickup stop from this list." wrapper="sm:col-span-2" />
        </form>
        <x-slot:footer><x-button form="add-route-form" icon="plus">Add route</x-button></x-slot:footer>
    </x-modal>

    <x-modal name="add-vehicle" title="Add vehicle" description="A school bus or van and its driver." icon="truck">
        <form method="POST" action="{{ route('transport.vehicles.store') }}" id="add-vehicle-form" class="grid gap-5 sm:grid-cols-2">
            @csrf
            <x-modal.form-name name="add-vehicle" />
            <x-form.input name="registration_number" label="Registration number" required placeholder="e.g. 2A-1234" />
            <x-form.input name="model" label="Model" placeholder="e.g. Toyota Hiace" />
            <x-form.input name="capacity" type="number" min="1" max="200" label="Seats" required />
            <x-form.input name="driver_name" label="Driver name" icon="user" />
            <x-form.input name="driver_phone" type="tel" label="Driver phone" icon="phone" />
        </form>
        <x-slot:footer><x-button form="add-vehicle-form" icon="plus">Add vehicle</x-button></x-slot:footer>
    </x-modal>
</x-layouts.app>
