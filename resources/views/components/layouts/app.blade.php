@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title.' - ' : '' }}{{ setting('school_name') }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans text-slate-700 antialiased">
    <div x-data="{ sidebarOpen: false }" @keydown.escape.window="sidebarOpen = false" class="min-h-full">

        {{-- mobile sidebar --}}
        <div x-cloak x-show="sidebarOpen" class="relative z-40 lg:hidden">
            <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 bg-slate-900/50" @click="sidebarOpen = false"></div>
            <div x-show="sidebarOpen" x-transition:enter="transition duration-200" x-transition:enter-start="-translate-x-full"
                 x-transition:leave="transition duration-200" x-transition:leave-end="-translate-x-full"
                 class="fixed inset-y-0 left-0 flex w-72 flex-col bg-white shadow-xl">
                <button type="button" class="absolute top-4 right-4 rounded-md p-1 text-slate-400 hover:text-slate-600" @click="sidebarOpen = false">
                    <span class="sr-only">Close menu</span>
                    <x-icon name="x-mark" class="size-6" />
                </button>
                <x-sidebar />
            </div>
        </div>

        {{-- desktop sidebar --}}
        <aside class="hidden border-r border-slate-200 bg-white lg:fixed lg:inset-y-0 lg:flex lg:w-72 lg:flex-col">
            <x-sidebar />
        </aside>

        <div class="lg:pl-72">
            {{-- top bar --}}
            <header class="sticky top-0 z-30 flex h-16 items-center gap-4 border-b border-slate-200 bg-white/90 px-4 backdrop-blur sm:px-6 lg:px-8">
                <button type="button" class="-m-2 p-2 text-slate-500 lg:hidden" @click="sidebarOpen = true">
                    <span class="sr-only">Open menu</span>
                    <x-icon name="bars" class="size-6" />
                </button>

                <div class="flex-1"></div>

                <x-dropdown width="208">
                    <x-slot:trigger>
                    <button type="button" class="flex items-center gap-3 rounded-full py-1 pr-2 pl-1 transition hover:bg-slate-100 focus-visible:outline-2 focus-visible:outline-primary-500">
                        <img class="size-8 rounded-full object-cover" src="{{ auth()->user()->avatar_url }}" alt="">
                        <span class="hidden text-left sm:block">
                            <span class="block text-sm font-semibold text-slate-900">{{ auth()->user()->name }}</span>
                            <span class="block text-xs text-slate-500">{{ auth()->user()->role?->label() }}</span>
                        </span>
                        <x-icon name="chevron-down" class="size-4 text-slate-400 transition" ::class="open && 'rotate-180'" />
                    </button>
                    </x-slot:trigger>

                    <div class="border-b border-slate-100 px-3 pt-1.5 pb-2.5 sm:hidden">
                        <p class="truncate text-sm font-semibold text-slate-900">{{ auth()->user()->name }}</p>
                        <p class="truncate text-xs text-slate-500">{{ auth()->user()->role?->label() }}</p>
                    </div>
                    <x-dropdown.item :href="route('user/profile/page')" icon="user">My profile</x-dropdown.item>
                    <div class="my-1 border-t border-slate-100"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-dropdown.item type="submit" icon="logout" tone="danger">Log out</x-dropdown.item>
                    </form>
                </x-dropdown>
            </header>

            <main class="px-4 py-6 sm:px-6 lg:px-8">
                {{ $slot }}
            </main>
        </div>
    </div>

    <x-flash />
    <x-confirm-dialog />
</body>
</html>
