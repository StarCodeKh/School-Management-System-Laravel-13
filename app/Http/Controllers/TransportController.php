<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\TransportAssignment;
use App\Models\TransportRoute;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/** Vehicles, routes and which students ride them. */
class TransportController extends Controller
{
    public function index()
    {
        $routes = TransportRoute::with('vehicle')->withCount('assignments')->orderBy('name')->get();
        $vehicles = Vehicle::withCount('routes')->orderBy('registration_number')->get();

        return view('transport.index', compact('routes', 'vehicles'));
    }

    public function showRoute(TransportRoute $transportRoute)
    {
        $transportRoute->load(['vehicle', 'assignments.student.schoolClass', 'assignments.student.section']);
        $students = Student::doesntHave('transportAssignment')->orderBy('first_name')->get()
            ->mapWithKeys(fn (Student $student) => [$student->id => $student->full_name.' - '.$student->admission_id]);

        return view('transport.route', ['route' => $transportRoute, 'students' => $students]);
    }

    public function storeRoute(Request $request)
    {
        TransportRoute::create($this->routeRules($request));

        return back()->with('success', 'Route added.');
    }

    public function updateRoute(Request $request, TransportRoute $transportRoute)
    {
        $transportRoute->update($this->routeRules($request, $transportRoute));

        return back()->with('success', 'Route updated.');
    }

    public function destroyRoute(TransportRoute $transportRoute)
    {
        $transportRoute->delete(); // assignments cascade

        return redirect()->route('transport.index')->with('success', 'Route deleted.');
    }

    public function storeVehicle(Request $request)
    {
        Vehicle::create($this->vehicleRules($request));

        return back()->with('success', 'Vehicle added.');
    }

    public function updateVehicle(Request $request, Vehicle $vehicle)
    {
        $vehicle->update($this->vehicleRules($request, $vehicle));

        return back()->with('success', 'Vehicle updated.');
    }

    public function destroyVehicle(Vehicle $vehicle)
    {
        $vehicle->delete(); // routes keep running without a vehicle

        return back()->with('success', 'Vehicle removed.');
    }

    /** put a student on the route, within the vehicle's seat count */
    public function assign(Request $request, TransportRoute $transportRoute)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id|unique:transport_assignments,student_id',
            'pickup_stop' => 'nullable|string|max:255',
        ], ['student_id.unique' => 'This student is already on a route.']);

        if ($transportRoute->vehicle && $transportRoute->assignments()->count() >= $transportRoute->vehicle->capacity) {
            throw ValidationException::withMessages(['student_id' => "The vehicle on this route is full ({$transportRoute->vehicle->capacity} seats)."]);
        }

        $transportRoute->assignments()->create($validated);

        return back()->with('success', 'Student added to the route.');
    }

    public function unassign(TransportAssignment $assignment)
    {
        $assignment->delete();

        return back()->with('success', 'Student removed from the route.');
    }

    private function routeRules(Request $request, ?TransportRoute $ignore = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('transport_routes')->ignore($ignore?->id)],
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'fare' => 'nullable|numeric|min:0|max:999999',
            'stops' => 'nullable|string|max:2000',
        ]);

        return ['fare' => $validated['fare'] ?? 0] + $validated;
    }

    private function vehicleRules(Request $request, ?Vehicle $ignore = null): array
    {
        return $request->validate([
            'registration_number' => ['required', 'string', 'max:30', Rule::unique('vehicles')->ignore($ignore?->id)],
            'model' => 'nullable|string|max:255',
            'capacity' => 'required|integer|min:1|max:200',
            'driver_name' => 'nullable|string|max:255',
            'driver_phone' => 'nullable|string|max:30',
        ]);
    }
}
