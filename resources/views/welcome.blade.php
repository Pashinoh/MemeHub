<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MemeHub</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/memehub-icon.svg') }}">
    <link rel="shortcut icon" href="{{ asset('images/memehub-icon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 antialiased">
    <main class="mx-auto flex min-h-screen w-full max-w-4xl flex-col items-center justify-center px-6 py-12 text-center">
        <h1 class="text-4xl font-bold tracking-wide">MemeHub</h1>
        <p class="mt-3 text-sm text-slate-300">Drop memes, get laughs.</p>

        <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
            @auth
                <a href="{{ route('memes.index') }}" class="rounded-lg bg-slate-700 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-600 transition">Enter Feed</a>
            @else
                <a href="{{ route('login') }}" class="rounded-lg border border-slate-700 bg-slate-900 px-4 py-2 text-sm font-semibold text-slate-200 hover:bg-slate-800 transition">Login</a>
                <a href="{{ route('auth.google.redirect') }}" class="rounded-lg bg-slate-700 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-600 transition">Daftar</a>
            @endauth
        </div>
    </main>
</body>
</html>
