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
        <a href="{{ url('/home') }}" class="mt-8 inline-flex rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700">Back to Home</a>
    </div>
</body>
</html>
