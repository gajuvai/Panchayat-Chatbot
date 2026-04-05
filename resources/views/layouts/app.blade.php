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
                    <span
                        id="sidebar-notif-badge"
                        class="ml-auto bg-red-500 text-white text-xs rounded-full px-1.5 py-0.5 min-w-[1.25rem] text-center {{ auth()->user()->unreadNotifications->count() > 0 ? '' : 'hidden' }}"
                    >{{ auth()->user()->unreadNotifications->count() }}</span>
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

                {{-- Notification Bell --}}
                <div x-data="notificationBell()" x-init="init()" class="relative">
                    <button @click="toggleDropdown()" class="relative p-1.5 rounded-lg text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition focus:outline-none" aria-label="Notifications">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <span
                            x-show="count > 0"
                            x-text="count > 99 ? '99+' : count"
                            class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold rounded-full min-w-[1.1rem] h-[1.1rem] flex items-center justify-center px-0.5 leading-none"
                        ></span>
                    </button>

                    {{-- Dropdown --}}
                    <div
                        x-show="open"
                        @click.outside="open = false"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg border border-gray-100 z-50 origin-top-right"
                        style="display:none;"
                    >
                        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                            <span class="text-sm font-semibold text-gray-700">Notifications</span>
                            <form method="POST" action="{{ route('notifications.read-all') }}" x-show="count > 0">
                                @csrf @method('PATCH')
                                <button type="submit" class="text-xs text-indigo-600 hover:underline">Mark all read</button>
                            </form>
                        </div>

                        <ul class="divide-y divide-gray-50 max-h-72 overflow-y-auto">
                            <template x-if="notifications.length === 0">
                                <li class="px-4 py-6 text-sm text-gray-400 text-center">No notifications yet.</li>
                            </template>
                            <template x-for="n in notifications" :key="n.id">
                                <li :class="n.read_at === null ? 'bg-indigo-50' : 'bg-white'" class="px-4 py-3 hover:bg-gray-50 transition">
                                    <p class="text-sm font-medium text-gray-800 leading-snug" x-text="n.data.title || 'Notification'"></p>
                                    <p class="text-xs text-gray-500 mt-0.5 line-clamp-2" x-text="n.data.message || ''"></p>
                                    <p class="text-[11px] text-gray-400 mt-1" x-text="n.human_time"></p>
                                </li>
                            </template>
                        </ul>

                        <div class="px-4 py-2.5 border-t border-gray-100 text-center">
                            <a href="{{ route('notifications.index') }}" class="text-xs text-indigo-600 hover:underline font-medium">View all notifications</a>
                        </div>
                    </div>
                </div>
                {{-- /Notification Bell --}}
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

@auth
<script>
/**
 * Alpine.js component for the header notification bell.
 * Shared state is managed by the polling function below, which
 * dispatches a custom event ("notif:updated") whenever new data arrives.
 */
function notificationBell() {
    return {
        open: false,
        count: {{ auth()->user()->unreadNotifications()->count() }},
        notifications: [],

        init() {
            // Listen for poll updates from the global poller
            window.addEventListener('notif:updated', (e) => {
                this.count         = e.detail.count;
                this.notifications = e.detail.notifications;
            });

            // Trigger an immediate fetch so the dropdown is populated on load
            window.pollNotifications && window.pollNotifications();
        },

        toggleDropdown() {
            this.open = !this.open;
        },
    };
}

/**
 * Global polling function — runs every 30 seconds.
 * Updates both the header bell (via custom event) and the sidebar badge (via DOM).
 */
window.pollNotifications = function () {
    fetch('{{ route('notifications.unread') }}', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        },
        credentials: 'same-origin',
    })
    .then(res => {
        if (!res.ok) return;
        return res.json();
    })
    .then(data => {
        if (!data) return;

        // Update header bell via Alpine custom event
        window.dispatchEvent(new CustomEvent('notif:updated', { detail: data }));

        // Update sidebar badge
        const badge = document.getElementById('sidebar-notif-badge');
        if (badge) {
            if (data.count > 0) {
                badge.textContent = data.count > 99 ? '99+' : data.count;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        }
    })
    .catch(() => { /* silently ignore network errors during polling */ });
};

// Poll immediately on load, then every 30 seconds
window.pollNotifications();
setInterval(window.pollNotifications, 30000);
</script>
@endauth
</body>
</html>
