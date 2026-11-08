<?php

namespace App\Http\Controllers;

use App\Enums\Audience;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

/** School events calendar. */
class EventController extends Controller
{
    /** month calendar plus the upcoming list */
    public function index(Request $request)
    {
        $request->validate(['month' => 'nullable|date_format:Y-m']);
        $user = $request->user();
        $month = Carbon::createFromFormat('Y-m-d', $request->query('month', now()->format('Y-m')).'-01')->startOfDay();
        $gridStart = $month->copy()->startOfWeek(Carbon::MONDAY);
        $gridEnd = $month->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $visible = fn ($q) => $user->can('manage-communication') ? $q : $q->visibleTo($user);

        $events = Event::query()->tap($visible)
            ->whereDate('starts_on', '<=', $gridEnd)
            ->where(fn ($q) => $q->whereDate('ends_on', '>=', $gridStart)->orWhere(fn ($q) => $q->whereNull('ends_on')->whereDate('starts_on', '>=', $gridStart)))
            ->orderBy('starts_on')
            ->get();

        // [Y-m-d => events on that day], multi-day events appear on each day
        $days = collect();
        for ($day = $gridStart->copy(); $day <= $gridEnd; $day->addDay()) {
            $days[$day->toDateString()] = $events->filter(fn (Event $event) => $day->between($event->starts_on, $event->endsOn()))->values();
        }

        $upcoming = Event::query()->tap($visible)->upcoming()->take(8)->get();

        return view('events.index', compact('month', 'days', 'upcoming'));
    }

    public function create()
    {
        return view('events.form', ['event' => null]);
    }

    public function store(Request $request)
    {
        $event = Event::create($this->validated($request) + ['created_by' => $request->user()->id]);

        return redirect()->route('events.index', ['month' => $event->starts_on->format('Y-m')])->with('success', 'Event added.');
    }

    public function edit(Event $event)
    {
        return view('events.form', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        $event->update($this->validated($request));

        return redirect()->route('events.index', ['month' => $event->starts_on->format('Y-m')])->with('success', 'Event updated.');
    }

    public function destroy(Event $event)
    {
        $event->delete();

        return redirect()->route('events.index')->with('success', 'Event deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'location' => 'nullable|string|max:255',
            'starts_on' => 'required|date',
            'ends_on' => 'nullable|date|after_or_equal:starts_on',
            'audience' => ['required', Rule::enum(Audience::class)],
        ]);
    }
}
