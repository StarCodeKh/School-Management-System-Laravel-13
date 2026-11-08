<?php

namespace App\View\Components;

use App\Models\Event;
use App\Models\Notice;
use Illuminate\View\Component;

/** Latest notices and upcoming events for the logged-in user, shown on every dashboard. */
class Noticeboard extends Component
{
    public function render()
    {
        $user = auth()->user();

        return view('components.noticeboard', [
            'notices' => Notice::visibleTo($user)->orderByDesc('is_pinned')->latest('published_at')->take(4)->get(),
            'events' => Event::visibleTo($user)->upcoming()->take(4)->get(),
        ]);
    }
}
