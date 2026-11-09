{{--
    Empty-state row for <x-table>. When the list is filtered (search, class, ...) it says so and offers to clear them.
    Extra content (e.g. an "Add student" button) goes in the slot.
--}}
@props(['colspan', 'message' => 'No records found.', 'icon' => 'document', 'filters' => ['search', 'class', 'category', 'customers', 'categories', 'from', 'to']])

@php($filtered = collect(request()->only($filters))->filter(fn ($value) => filled($value))->isNotEmpty())

<tr>
    <td colspan="{{ $colspan }}" class="py-12! whitespace-normal! text-center">
        <div class="mx-auto flex max-w-sm flex-col items-center gap-2">
            <span class="grid size-11 place-items-center rounded-full bg-slate-100 text-slate-400">
                <x-icon :name="$filtered ? 'search' : $icon" class="size-5" />
            </span>
            <p class="font-medium text-slate-700">{{ $message }}</p>
            @if ($filtered)
                <p class="text-sm text-slate-500">
                    Try another search, or
                    <a href="{{ url()->current() }}" data-live-link class="font-medium text-primary-600 hover:underline">clear the filters</a>.
                </p>
            @endif
            {{ $slot }}
        </div>
    </td>
</tr>
