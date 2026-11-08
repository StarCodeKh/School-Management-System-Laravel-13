<x-layouts.app title="Notice Board">
    <x-page-header title="Notice board" :breadcrumbs="['Dashboard' => route('home'), 'Notices' => null]">
        <x-slot:actions>
            @can('manage-communication')
                <x-button :href="route('notices.create')" icon="plus">New notice</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="mx-auto max-w-3xl space-y-4">
        @forelse ($notices as $notice)
            <article x-data="{ open: {{ $loop->first ? 'true' : 'false' }} }" @class(['rounded-xl border bg-white p-5 shadow-sm', 'border-primary-200 ring-1 ring-primary-100' => $notice->is_pinned, 'border-slate-200' => ! $notice->is_pinned])>
                <header class="flex flex-wrap items-start justify-between gap-3">
                    <button type="button" class="flex-1 text-left" @click="open = !open">
                        <h2 class="text-base font-semibold text-slate-900">{{ $notice->title }}</h2>
                        <p class="mt-0.5 text-xs text-slate-500">{{ $notice->published_at->format('d M Y, H:i') }} &middot; {{ $notice->author?->name ?? 'School office' }}</p>
                    </button>
                    <div class="flex items-center gap-1.5">
                        @if ($notice->is_pinned)<x-badge color="indigo">Pinned</x-badge>@endif
                        <x-badge :color="$notice->audience->color()">{{ $notice->audience->label() }}</x-badge>
                        @can('manage-communication')
                            @unless ($notice->isActive())<x-badge>{{ $notice->published_at->isFuture() ? 'Scheduled' : 'Expired' }}</x-badge>@endunless
                            <x-icon-link :href="route('notices.edit', $notice)" icon="pencil" label="Edit" tone="primary" />
                            <x-delete-button :action="route('notices.destroy', $notice)" :fields="['_method' => 'DELETE']" title="Delete notice" :message="'Delete the notice: '.$notice->title.'?'" />
                        @endcan
                    </div>
                </header>
                <div x-show="open" x-cloak class="mt-3 border-t border-slate-100 pt-3 text-sm whitespace-pre-line text-slate-700">{{ $notice->body }}</div>
                @if ($notice->expires_on)<p x-show="open" x-cloak class="mt-3 text-xs text-slate-400">Until {{ $notice->expires_on->format('d M Y') }}</p>@endif
            </article>
        @empty
            <p class="rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center text-sm text-slate-500">No notices right now.</p>
        @endforelse
        {{ $notices->links() }}
    </div>
</x-layouts.app>
