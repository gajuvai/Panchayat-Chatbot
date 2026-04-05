<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('code', 'Error') — @yield('title', 'Something went wrong') | {{ config('app.name', 'Panchayat') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css'])
    {{-- Inline critical styles so the page is usable even if Vite assets fail --}}
    <style>
        body { font-family: 'Figtree', system-ui, sans-serif; }
        .error-page { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #f9fafb; }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-indigo-50/30 to-slate-100 min-h-screen font-sans antialiased">

<div class="min-h-screen flex flex-col">
    {{-- Top bar --}}
    <header class="bg-white/80 backdrop-blur border-b border-gray-100 px-6 py-4">
        <a href="{{ url('/') }}" class="inline-flex items-center gap-2.5 text-indigo-900 hover:text-indigo-700 transition">
            <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center text-white font-bold text-sm">P</div>
            <span class="font-semibold text-sm">{{ config('app.name', 'Panchayat') }}</span>
        </a>
    </header>

    {{-- Error content --}}
    <div class="flex-1 flex items-center justify-center px-4 py-16">
        <div class="text-center max-w-lg w-full">
            @yield('body')
        </div>
    </div>

    {{-- Footer --}}
    <footer class="py-6 text-center text-xs text-gray-400">
        &copy; {{ date('Y') }} {{ config('app.name', 'Panchayat') }} — Community Management Platform
    </footer>
</div>

</body>
</html>
