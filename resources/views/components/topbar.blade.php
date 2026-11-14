{{--
    Top bar: menu button (phones), search box (pages + records, Ctrl/Cmd+K), today's date, notices bell and user menu.
    $pages and $notices come from the view composer in AppServiceProvider; the search box is commandPalette in app.js.
--}}
@php
    $user = auth()->user();
    $latestNotice = $notices->max('published_at');
@endphp

<header class="sticky top-0 z-30 flex h-16 items-center gap-3 border-b border-slate-200 bg-white/90 px-4 backdrop-blur sm:gap-4 sm:px-6 lg:px-8">
    <button type="button" class="-m-2 rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-700 lg:hidden" @click="sidebarOpen = true">
        <span class="sr-only">Open menu</span>
        <x-icon name="bars" class="size-6" />
    </button>

    {{-- search --}}
    <div x-data="commandPalette({ pages: @js($pages), endpoint: @js(route('search')) })" class="min-w-0 flex-1"
         @keydown.window.prevent.meta.k="openPalette()" @keydown.window.prevent.ctrl.k="openPalette()">
        <button type="button" @click="openPalette()"
                class="flex w-full max-w-md items-center gap-2.5 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-400 transition hover:border-slate-300 hover:bg-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-500">
            <x-icon name="search" class="size-4 shrink-0" />
            <span class="truncate"><span class="sm:hidden">Search</span><span class="hidden sm:inline">Search pages, students, teachers...</span></span>
            <kbd class="ml-auto hidden rounded border border-slate-200 bg-white px-1.5 font-sans text-[11px] font-medium text-slate-500 sm:block" x-text="shortcut">Ctrl K</kbd>
        </button>

        <template x-teleport="body">
            <div x-show="open" x-cloak class="fixed inset-0 z-[80] flex items-start justify-center p-4 pt-[12vh]" role="dialog" aria-modal="true" aria-label="Search">
                <div x-show="open" x-transition.opacity class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="closePalette()"></div>
                <div x-show="open" x-transition:enter="transition duration-150 ease-out" x-transition:enter-start="scale-95 opacity-0" x-transition:enter-end="scale-100 opacity-100"
                     class="relative w-full max-w-xl overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-slate-900/10" @keydown="onKey($event)">
                    <div class="flex items-center gap-3 border-b border-slate-100 px-4">
                        <x-icon name="search" class="size-5 shrink-0 text-slate-400" />
                        <input x-ref="input" x-model="query" @input.debounce.250ms="fetchRecords()" type="text" autocomplete="off" spellcheck="false"
                               placeholder="Search pages, students, teachers..." class="h-14 w-full border-0 bg-transparent px-0 text-base text-slate-900 placeholder:text-slate-400 focus:ring-0">
                        <span x-show="loading" class="size-4 shrink-0 animate-spin rounded-full border-2 border-slate-200 border-t-primary-500"></span>
                        <kbd class="hidden rounded border border-slate-200 px-1.5 text-[11px] font-medium text-slate-400 sm:block">Esc</kbd>
                    </div>

                    <div x-ref="list" class="max-h-[60vh] overflow-y-auto p-2">
                        <template x-for="group in groups" :key="group.label">
                            <div class="mb-1">
                                <p class="px-3 pt-2 pb-1 text-xs font-semibold tracking-wide text-slate-400 uppercase" x-text="group.label"></p>
                                <template x-for="item in group.items" :key="item.url">
                                    <a :href="item.url" :data-index="item.index" @mousemove="active = item.index"
                                       class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm"
                                       :class="active === item.index ? 'bg-primary-50 text-primary-700' : 'text-slate-700'">
                                        <template x-if="item.image"><img :src="item.image" alt="" class="size-8 shrink-0 rounded-full object-cover"></template>
                                        <template x-if="! item.image">
                                            <span class="grid size-8 shrink-0 place-items-center rounded-lg bg-slate-100 text-slate-500" x-html="icons[item.icon] ?? icons.document"></span>
                                        </template>
                                        <span class="min-w-0 flex-1">
                                            <span class="block truncate font-medium" x-text="item.title"></span>
                                            <span class="block truncate text-xs text-slate-500" x-show="item.meta" x-text="item.meta"></span>
                                        </span>
                                        <x-icon name="chevron-right" class="size-4 shrink-0 text-slate-300" x-show="active === item.index" />
                                    </a>
                                </template>
                            </div>
                        </template>

                        <div x-show="! groups.length && ! loading" class="px-4 py-10 text-center text-sm text-slate-500">
                            <span x-show="query.trim()">No results for "<span class="font-medium text-slate-700" x-text="query.trim()"></span>".</span>
                            <span x-show="! query.trim()">Type to search.</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 border-t border-slate-100 bg-slate-50 px-4 py-2 text-xs text-slate-500">
                        <span><kbd class="font-sans font-semibold">↑ ↓</kbd> to move</span>
                        <span><kbd class="font-sans font-semibold">Enter</kbd> to open</span>
                        <span class="ml-auto hidden sm:inline">Records you can open are listed after pages.</span>
                    </div>
                </div>
            </div>
        </template>

        {{-- page icons for results, rendered once and copied into the list by name --}}
        <div class="hidden" x-ref="icons">
            @foreach ($pages->pluck('icon')->push('document')->filter()->unique() as $icon)
                <span data-icon="{{ $icon }}"><x-icon :name="$icon" class="size-4" /></span>
            @endforeach
        </div>
    </div>

    {{-- today --}}
    <div class="hidden items-center gap-2 text-sm text-slate-500 xl:flex">
        <x-icon name="calendar" class="size-4 text-slate-400" />
        <span>{{ now()->format('l, d M Y') }}</span>
    </div>

    {{-- notices bell --}}
    <x-dropdown width="320">
        <x-slot:trigger>
            <button type="button" x-data="noticeBell(@js($latestNotice?->toIso8601String()))" @click="markSeen()"
                    class="relative grid size-10 place-items-center rounded-full text-slate-500 transition hover:bg-slate-100 hover:text-slate-700 focus-visible:outline-2 focus-visible:outline-primary-500">
                <span class="sr-only">Notices</span>
                <x-icon name="megaphone" class="size-5" />
                <span x-show="unread" x-cloak class="absolute top-2 right-2 size-2.5 rounded-full bg-red-500 ring-2 ring-white"></span>
            </button>
        </x-slot:trigger>

        <div class="flex items-center justify-between px-3 pt-2 pb-1.5">
            <p class="text-sm font-semibold text-slate-900">Notices</p>
            <a href="{{ route('notices.index') }}" class="text-xs font-medium text-primary-600 hover:underline">View all</a>
        </div>
        @forelse ($notices as $notice)
            <a href="{{ route('notices.index') }}" role="menuitem" class="block rounded-lg px-3 py-2 hover:bg-slate-50 focus:bg-slate-50 focus:outline-none">
                <span class="flex items-center gap-1.5 text-sm font-medium text-slate-900">
                    @if ($notice->is_pinned)<x-icon name="tag" class="size-3.5 shrink-0 text-amber-500" />@endif
                    <span class="truncate">{{ $notice->title }}</span>
                </span>
                <span class="mt-0.5 line-clamp-2 block text-xs text-slate-500">{{ \Illuminate\Support\Str::limit(strip_tags($notice->body), 110) }}</span>
                <span class="mt-1 block text-[11px] text-slate-400">{{ $notice->published_at->diffForHumans() }}</span>
            </a>
        @empty
            <p class="px-3 py-6 text-center text-sm text-slate-500">No notices right now.</p>
        @endforelse
    </x-dropdown>

    {{-- user menu --}}
    <x-dropdown width="240">
        <x-slot:trigger>
            <button type="button"
                    class="flex items-center gap-2.5 rounded-full border border-transparent py-1 pr-2.5 pl-1 transition hover:border-slate-200 hover:bg-slate-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-500"
                    :class="open && 'border-slate-200 bg-slate-50'">
                <span class="relative">
                    <img class="size-8 rounded-full object-cover ring-1 ring-slate-200" src="{{ $user->avatar_url }}" alt="">
                    <span class="absolute -right-0.5 -bottom-0.5 size-2.5 rounded-full bg-emerald-500 ring-2 ring-white"></span>
                </span>
                <span class="hidden max-w-40 text-left sm:block">
                    <span class="block truncate text-sm leading-5 font-semibold text-slate-900">{{ $user->name }}</span>
                    <span class="block truncate text-xs leading-4 text-slate-500">{{ $user->role?->label() }}</span>
                </span>
                <x-icon name="chevron-down" class="size-4 text-slate-400 transition" ::class="open && 'rotate-180'" />
            </button>
        </x-slot:trigger>

        <div class="flex items-center gap-3 border-b border-slate-100 px-3 pt-2 pb-3">
            <img class="size-10 rounded-full object-cover" src="{{ $user->avatar_url }}" alt="">
            <div class="min-w-0">
                <p class="truncate text-sm font-semibold text-slate-900">{{ $user->name }}</p>
                <p class="truncate text-xs text-slate-500">{{ $user->email }}</p>
                <x-badge :color="$user->role?->color() ?? 'slate'" class="mt-1">{{ $user->role?->label() }}</x-badge>
            </div>
        </div>
        <div class="py-1">
            <x-dropdown.item :href="route('user/profile/page')" icon="user">My profile</x-dropdown.item>
            @can('manage-settings')
                <x-dropdown.item :href="route('setting/page')" icon="cog">Settings</x-dropdown.item>
            @endcan
        </div>
        <div class="border-t border-slate-100 pt-1">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-dropdown.item type="submit" icon="logout" tone="danger">Log out</x-dropdown.item>
            </form>
        </div>
    </x-dropdown>
</header>
