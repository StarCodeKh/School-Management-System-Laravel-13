@props(['code', 'title', 'message'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $code }} - {{ $title }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="grid h-full place-items-center px-6 font-sans antialiased">
    <div class="text-center">
        <p class="text-6xl font-bold text-primary-600">{{ $code }}</p>
        <h1 class="mt-4 text-2xl font-bold tracking-tight text-slate-900">{{ $title }}</h1>
        <p class="mt-2 text-slate-500">{{ $message }}</p>
        <div class="mt-8 flex justify-center gap-2">
            <x-button variant="secondary" icon="arrow-left" type="button" onclick="history.length > 1 ? history.back() : location.assign('{{ url('/home') }}')">Go back</x-button>
            <x-button :href="url('/home')" icon="home">Back to home</x-button>
        </div>
    </div>
</body>
</html>
