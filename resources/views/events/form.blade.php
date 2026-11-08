<x-layouts.app :title="$event ? 'Edit Event' : 'New Event'">
    <x-page-header :title="$event ? 'Edit event' : 'New event'" :breadcrumbs="['Events' => route('events.index'), ($event ? 'Edit' : 'New') => null]" />

    <x-card class="max-w-3xl">
        <form method="POST" action="{{ $event ? route('events.update', $event) : route('events.store') }}" class="space-y-6">
            @csrf
            @if ($event) @method('PUT') @endif
            <div class="grid gap-5 sm:grid-cols-2">
                <x-form.input name="title" label="Title" :value="$event?->title" required wrapper="sm:col-span-2" placeholder="e.g. Sports Day" />
                <x-form.input name="starts_on" type="date" label="Starts" :value="$event?->starts_on" required />
                <x-form.input name="ends_on" type="date" label="Ends" :value="$event?->ends_on" hint="Leave empty for a one-day event." />
                <x-form.input name="location" label="Location" :value="$event?->location" />
                <x-form.select name="audience" label="Audience" :options="\App\Enums\Audience::options()" :value="$event?->audience ?? 'everyone'" :placeholder="false" required />
                <x-form.textarea name="description" label="Description" :value="$event?->description" rows="4" wrapper="sm:col-span-2" />
            </div>
            <x-form.actions :submit="$event ? 'Update event' : 'Add event'" :cancel="route('events.index')" />
        </form>
    </x-card>
</x-layouts.app>
