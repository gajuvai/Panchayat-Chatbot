<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Panchayat') }} - @yield('title', 'Dashboard')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans antialiased">
<div class="min-h-screen flex">
    {{-- Sidebar --}}
    <aside class="w-64 bg-indigo-900 text-white flex flex-col shadow-xl flex-shrink-0">
        <div class="p-5 border-b border-indigo-700">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-500 rounded-full flex items-center justify-center font-bold text-lg">P</div>
                <div>
                    <div class="font-bold text-sm leading-tight">Panchayat</div>
                    <div class="text-indigo-300 text-xs">{{ ucfirst(str_replace('_',' ', auth()->user()->role?->name ?? 'user')) }}</div>
                </div>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto p-3 space-y-1 text-sm">
            @php $user = auth()->user(); @endphp
            @if($user->isResident())
                @include('layouts.partials.resident-nav')
            @elseif($user->isAdmin())
                @include('layouts.partials.admin-nav')
            @elseif($user->isSecurityHead())
                @include('layouts.partials.security-nav')
            @endif

            <div class="border-t border-indigo-700 pt-2 mt-2 space-y-1">
                <a href="{{ route('notifications.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg {{ request()->routeIs('notifications.*') ? 'bg-indigo-700 text-white' : 'text-indigo-200 hover:bg-indigo-800 hover:text-white' }} transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    <span>Notifications</span>
                    @if(auth()->user()->unreadNotifications->count() > 0)
                        <span class="ml-auto bg-red-500 text-white text-xs rounded-full px-1.5 py-0.5 min-w-[1.25rem] text-center">{{ auth()->user()->unreadNotifications->count() }}</span>
                    @endif
                </a>
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg {{ request()->routeIs('profile.*') ? 'bg-indigo-700 text-white' : 'text-indigo-200 hover:bg-indigo-800 hover:text-white' }} transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Profile
                </a>
            </div>
        </nav>

        <div class="p-3 border-t border-indigo-700">
            <div class="text-indigo-300 text-xs px-2 mb-1 truncate">{{ auth()->user()->name }}</div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-2 text-indigo-300 hover:text-white text-sm px-3 py-2 rounded-lg hover:bg-indigo-700 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Logout
                </button>
            </form>
        </div>
    </aside>

    {{-- Main content --}}
    <div class="flex-1 flex flex-col min-w-0">
        <header class="bg-white shadow-sm px-6 py-4 flex items-center justify-between">
            <h1 class="text-xl font-semibold text-gray-800">@yield('title', 'Dashboard')</h1>
            <div class="flex items-center gap-3 text-sm text-gray-500">
                @if(auth()->user()->flat_number)
                    <span class="bg-indigo-100 text-indigo-700 px-2 py-1 rounded text-xs font-medium">
                        Flat {{ auth()->user()->block }}-{{ auth()->user()->flat_number }}
                    </span>
                @endif
                <span>{{ auth()->user()->name }}</span>
            </div>
        </header>

        <div class="px-6 pt-4">
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-4 flex items-center gap-2 text-sm">
                    <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-4 text-sm">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-4 text-sm">
                    <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif
        </div>

        <main class="flex-1 px-6 pb-6">
            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
