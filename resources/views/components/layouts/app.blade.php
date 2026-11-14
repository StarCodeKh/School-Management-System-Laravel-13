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
                 class="fixed inset-y-0 left-0 flex w-72 max-w-[calc(100%-4rem)] flex-col bg-white shadow-xl">
                {{-- sits on the dimmed backdrop beside the panel, so it never covers the school name --}}
                <button type="button" class="absolute top-3.5 left-full ml-3 grid size-9 place-items-center rounded-lg text-white/80 hover:bg-white/10 hover:text-white focus-visible:outline-2 focus-visible:outline-white" @click="sidebarOpen = false">
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
            <x-topbar />

            <main class="px-4 py-6 sm:px-6 lg:px-8">
                {{ $slot }}
            </main>
        </div>
    </div>

    <x-flash />
    <x-confirm-dialog />
</body>
</html>
