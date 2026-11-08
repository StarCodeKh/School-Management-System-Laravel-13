@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ? $title.' - ' : '' }}{{ setting('school_name') }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans text-slate-700 antialiased">
    <div class="flex min-h-full flex-col justify-center px-4 py-12 sm:px-6">
        <div class="mx-auto w-full max-w-md">
            <a href="{{ url('/') }}" class="flex items-center justify-center gap-3">
                <span class="grid size-11 place-items-center rounded-xl bg-primary-600 text-white">
                    <x-icon name="academic-cap" class="size-6" />
                </span>
                <span class="text-lg font-bold text-slate-900">{{ setting('school_name') }}</span>
            </a>

            <div class="mt-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                {{ $slot }}
            </div>
        </div>
    </div>

    <x-flash />
</body>
</html>
