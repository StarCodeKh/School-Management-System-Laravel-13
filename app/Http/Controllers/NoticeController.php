<?php

namespace App\Http\Controllers;

use App\Enums\Audience;
use App\Models\Notice;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Notice board: everyone reads what is meant for them; admins publish. */
class NoticeController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $notices = Notice::with('author')
            ->when($user->can('manage-communication'), fn ($q) => $q, fn ($q) => $q->visibleTo($user))
            ->orderByDesc('is_pinned')
            ->latest('published_at')
            ->paginate(10);

        return view('notices.index', compact('notices'));
    }

    public function create()
    {
        return view('notices.form', ['notice' => null]);
    }

    public function store(Request $request)
    {
        Notice::create($this->validated($request) + ['created_by' => $request->user()->id]);

        return redirect()->route('notices.index')->with('success', 'Notice published.');
    }

    public function edit(Notice $notice)
    {
        return view('notices.form', compact('notice'));
    }

    public function update(Request $request, Notice $notice)
    {
        $notice->update($this->validated($request));

        return redirect()->route('notices.index')->with('success', 'Notice updated.');
    }

    public function destroy(Notice $notice)
    {
        $notice->delete();

        return redirect()->route('notices.index')->with('success', 'Notice deleted.');
    }

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:5000',
            'audience' => ['required', Rule::enum(Audience::class)],
            'published_at' => 'nullable|date',
            'expires_on' => 'nullable|date|after_or_equal:today',
        ]);

        return ['is_pinned' => $request->boolean('is_pinned'), 'published_at' => $validated['published_at'] ?? now()] + $validated;
    }
}
