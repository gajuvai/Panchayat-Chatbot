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
    {{-- Apply dark mode before first paint to prevent flash --}}
    <script>
        (function () {
            var stored = localStorage.getItem('dark_mode');
            var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (stored === '1' || (stored === null && prefersDark)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 font-sans antialiased transition-colors duration-200">
<div class="min-h-screen flex">

    {{-- ─── Sidebar ─────────────────────────────────────────────────── --}}
    <aside class="w-64 bg-indigo-900 text-white flex flex-col shadow-xl flex-shrink-0">

        {{-- Brand --}}
        <div class="p-5 border-b border-indigo-700 flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-500 rounded-full flex items-center justify-center font-bold text-lg">P</div>
                <div>
                    <div class="font-bold text-sm leading-tight">Panchayat</div>
                    <div class="text-indigo-300 text-xs">{{ ucfirst(str_replace('_',' ', auth()->user()->role?->name ?? 'user')) }}</div>
                </div>
            </div>
        </div>

        {{-- Scrollable role-specific navigation --}}
        <nav class="flex-1 overflow-y-auto p-3 space-y-1 text-sm">
            @php $user = auth()->user(); @endphp
            @if($user->isResident())
                @include('layouts.partials.resident-nav')
            @elseif($user->isAdmin())
                @include('layouts.partials.admin-nav')
            @elseif($user->isSecurityHead())
                @include('layouts.partials.security-nav')
            @endif
        </nav>

        {{-- Fixed bottom: Notifications + Profile (always visible) --}}
        <div class="border-t border-indigo-700 p-3 space-y-1 text-sm flex-shrink-0">
            <a href="{{ route('notifications.index') }}"
               class="flex items-center gap-2 px-3 py-2 rounded-lg {{ request()->routeIs('notifications.*') ? 'bg-indigo-700 text-white' : 'text-indigo-200 hover:bg-indigo-800 hover:text-white' }} transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <span>Notifications</span>
                <span id="sidebar-notif-badge"
                    class="ml-auto bg-red-500 text-white text-xs rounded-full px-1.5 py-0.5 min-w-[1.25rem] text-center {{ auth()->user()->unreadNotifications->count() > 0 ? '' : 'hidden' }}">
                    {{ auth()->user()->unreadNotifications->count() }}
                </span>
            </a>
            <a href="{{ route('profile.edit') }}"
               class="flex items-center gap-2 px-3 py-2 rounded-lg {{ request()->routeIs('profile.*') ? 'bg-indigo-700 text-white' : 'text-indigo-200 hover:bg-indigo-800 hover:text-white' }} transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Profile
            </a>
            <a href="{{ route('notifications.preferences') }}"
               class="flex items-center gap-2 px-3 py-2 rounded-lg {{ request()->routeIs('notifications.preferences*') ? 'bg-indigo-700 text-white' : 'text-indigo-200 hover:bg-indigo-800 hover:text-white' }} transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Alerts
            </a>
        </div>

        {{-- Fixed bottom: User + Logout --}}
        <div class="p-3 border-t border-indigo-700 flex-shrink-0">
            <div class="text-indigo-300 text-xs px-2 mb-1 truncate">{{ auth()->user()->name }}</div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-2 text-indigo-300 hover:text-white text-sm px-3 py-2 rounded-lg hover:bg-indigo-700 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Logout
                </button>
            </form>
        </div>
    </aside>

    {{-- ─── Main content ─────────────────────────────────────────────── --}}
    <div class="flex-1 flex flex-col min-w-0">

        {{-- Header --}}
        <header class="bg-white dark:bg-gray-800 shadow-sm dark:shadow-gray-900 px-6 py-4 flex items-center justify-between flex-shrink-0 transition-colors duration-200">
            <h1 class="text-xl font-semibold text-gray-800 dark:text-gray-100">@yield('title', 'Dashboard')</h1>

            <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                @if(auth()->user()->flat_number)
                    <span class="bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 px-2 py-1 rounded text-xs font-medium">
                        Flat {{ auth()->user()->block }}-{{ auth()->user()->flat_number }}
                    </span>
                @endif
                <span class="hidden sm:inline">{{ auth()->user()->name }}</span>

                {{-- Dark mode toggle --}}
                <div x-data="darkModeToggle()" x-init="init()">
                    <button @click="toggle()"
                        :title="dark ? 'Switch to light mode' : 'Switch to dark mode'"
                        class="p-1.5 rounded-lg transition focus:outline-none text-gray-400 dark:text-yellow-400 hover:bg-gray-100 dark:hover:bg-gray-700">
                        {{-- Moon (shown in light mode → click to go dark) --}}
                        <template x-if="!dark">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                            </svg>
                        </template>
                        {{-- Sun (shown in dark mode → click to go light) --}}
                        <template x-if="dark">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z"/>
                            </svg>
                        </template>
                    </button>
                </div>

                {{-- Sound toggle --}}
                <div x-data="soundToggle()" x-init="init()">
                    <button @click="toggle()"
                        :title="enabled ? 'Mute notification sound' : 'Enable notification sound'"
                        class="p-1.5 rounded-lg transition focus:outline-none"
                        :class="enabled
                            ? 'text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-gray-700'
                            : 'text-gray-300 dark:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700'">
                        <template x-if="enabled">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5L6 9H2v6h4l5 4V5z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072"/>
                            </svg>
                        </template>
                        <template x-if="!enabled">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" clip-rule="evenodd"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/>
                            </svg>
                        </template>
                    </button>
                </div>

                {{-- Notification Bell --}}
                <div x-data="notificationBell()" x-init="init()" class="relative">
                    <button @click="toggleDropdown()"
                        class="relative p-1.5 rounded-lg text-gray-400 dark:text-gray-500 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-gray-700 transition focus:outline-none"
                        aria-label="Notifications">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <span x-show="count > 0" x-text="count > 99 ? '99+' : count"
                            class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold rounded-full min-w-[1.1rem] h-[1.1rem] flex items-center justify-center px-0.5 leading-none">
                        </span>
                    </button>

                    {{-- Dropdown --}}
                    <div x-show="open" @click.outside="open = false"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 z-50 origin-top-right"
                        style="display:none;">
                        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Notifications</span>
                            <form method="POST" action="{{ route('notifications.read-all') }}" x-show="count > 0">
                                @csrf @method('PATCH')
                                <button type="submit" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">Mark all read</button>
                            </form>
                        </div>

                        <ul class="divide-y divide-gray-50 dark:divide-gray-700 max-h-72 overflow-y-auto">
                            <template x-if="notifications.length === 0">
                                <li class="px-4 py-6 text-sm text-gray-400 dark:text-gray-500 text-center">No notifications yet.</li>
                            </template>
                            <template x-for="n in notifications" :key="n.id">
                                <li :class="n.read_at === null ? 'bg-indigo-50 dark:bg-indigo-900/30' : 'bg-white dark:bg-gray-800'"
                                    class="px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                    <p class="text-sm font-medium text-gray-800 dark:text-gray-100 leading-snug" x-text="n.data.title || 'Notification'"></p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 line-clamp-2" x-text="n.data.message || ''"></p>
                                    <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-1" x-text="n.human_time"></p>
                                </li>
                            </template>
                        </ul>

                        <div class="px-4 py-2.5 border-t border-gray-100 dark:border-gray-700 text-center">
                            <a href="{{ route('notifications.index') }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline font-medium">View all notifications</a>
                        </div>
                    </div>
                </div>
                {{-- /Notification Bell --}}
            </div>
        </header>

        {{-- Flash messages --}}
        <div class="px-6 pt-4">
            @if(session('success'))
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-300 px-4 py-3 rounded-lg mb-4 flex items-center gap-2 text-sm">
                    <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 px-4 py-3 rounded-lg mb-4 text-sm">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 px-4 py-3 rounded-lg mb-4 text-sm">
                    <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif
        </div>

        <main class="flex-1 px-6 pb-6">
            @yield('content')
        </main>
    </div>
</div>

{{-- Toast container --}}
<div id="notif-toast-container" class="fixed bottom-4 right-4 z-[200] flex flex-col gap-2 pointer-events-none" style="max-width:22rem;"></div>

@auth
<script>
// ─── Dark mode Alpine component ───────────────────────────────────────────────
function darkModeToggle() {
    return {
        dark: false,
        init() {
            this.dark = document.documentElement.classList.contains('dark');
        },
        toggle() {
            this.dark = !this.dark;
            document.documentElement.classList.toggle('dark', this.dark);
            localStorage.setItem('dark_mode', this.dark ? '1' : '0');
        },
    };
}

// ─── Sound toggle Alpine component ───────────────────────────────────────────
function soundToggle() {
    return {
        enabled: true,
        init() {
            const stored = localStorage.getItem('notif_sound');
            this.enabled = stored === null ? true : stored === '1';
            window._notifSoundEnabled = this.enabled;
        },
        toggle() {
            this.enabled = !this.enabled;
            localStorage.setItem('notif_sound', this.enabled ? '1' : '0');
            window._notifSoundEnabled = this.enabled;
        },
    };
}

// ─── Web Audio chime ──────────────────────────────────────────────────────────
function playNotificationChime() {
    try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        [880, 1108].forEach((freq, i) => {
            const osc = ctx.createOscillator(), gain = ctx.createGain();
            osc.connect(gain); gain.connect(ctx.destination);
            osc.type = 'sine'; osc.frequency.value = freq;
            const t = ctx.currentTime + i * 0.18;
            gain.gain.setValueAtTime(0.001, t);
            gain.gain.linearRampToValueAtTime(0.25, t + 0.04);
            gain.gain.exponentialRampToValueAtTime(0.001, t + 0.35);
            osc.start(t); osc.stop(t + 0.38);
        });
    } catch (e) {}
}

// ─── Toast helper ─────────────────────────────────────────────────────────────
function showNotifToast(title, message) {
    const container = document.getElementById('notif-toast-container');
    if (!container) return;
    const safe = s => { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; };
    const isDark = document.documentElement.classList.contains('dark');

    const toast = document.createElement('div');
    toast.className = [
        'pointer-events-auto flex items-start gap-3 rounded-xl shadow-lg px-4 py-3 w-full',
        'transition-all duration-300 translate-y-4 opacity-0 border',
        isDark
            ? 'bg-gray-800 border-indigo-700 text-gray-100'
            : 'bg-white border-indigo-200 text-gray-800',
    ].join(' ');

    toast.innerHTML = `
        <div class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold leading-snug">${safe(title)}</p>
            <p class="text-xs opacity-70 mt-0.5 line-clamp-2">${safe(message)}</p>
        </div>
        <button class="opacity-40 hover:opacity-70 flex-shrink-0 mt-0.5 transition" aria-label="Dismiss">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>`;

    const dismiss = () => {
        toast.classList.add('opacity-0', 'translate-y-4');
        toast.addEventListener('transitionend', () => toast.remove(), { once: true });
    };
    toast.querySelector('button').addEventListener('click', dismiss);
    container.appendChild(toast);
    requestAnimationFrame(() => requestAnimationFrame(() => toast.classList.remove('translate-y-4', 'opacity-0')));
    setTimeout(dismiss, 5000);
}

// ─── Notification bell Alpine component ───────────────────────────────────────
function notificationBell() {
    return {
        open: false,
        count: {{ auth()->user()->unreadNotifications()->count() }},
        notifications: [],
        init() {
            window.addEventListener('notif:updated', e => {
                this.count         = e.detail.count;
                this.notifications = e.detail.notifications;
            });
            window.pollNotifications && window.pollNotifications();
        },
        toggleDropdown() { this.open = !this.open; },
    };
}

// ─── Global notification polling ─────────────────────────────────────────────
let _knownNotifIds = null, _isFirstPoll = true;

window.pollNotifications = function () {
    fetch('{{ route('notifications.unread') }}', {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        credentials: 'same-origin',
    })
    .then(r => { if (!r.ok) return; return r.json(); })
    .then(data => {
        if (!data) return;
        const incoming   = data.notifications || [];
        const incomingIds = new Set(incoming.map(n => n.id));

        if (_isFirstPoll) {
            _knownNotifIds = incomingIds;
            _isFirstPoll   = false;
        } else {
            const newOnes = incoming.filter(n => !_knownNotifIds.has(n.id));
            if (newOnes.length > 0) {
                if (window._notifSoundEnabled !== false) playNotificationChime();
                newOnes.slice(0, 3).forEach(n => showNotifToast(n.data?.title || 'New notification', n.data?.message || ''));
                if ('Notification' in window && Notification.permission === 'default') Notification.requestPermission();
                if ('Notification' in window && Notification.permission === 'granted') {
                    newOnes.slice(0, 3).forEach(n => new Notification(n.data?.title || 'Panchayat', { body: n.data?.message || '', icon: '/favicon.ico' }));
                }
                _knownNotifIds = incomingIds;
            }
        }

        window.dispatchEvent(new CustomEvent('notif:updated', { detail: data }));
        const badge = document.getElementById('sidebar-notif-badge');
        if (badge) {
            if (data.count > 0) { badge.textContent = data.count > 99 ? '99+' : data.count; badge.classList.remove('hidden'); }
            else badge.classList.add('hidden');
        }
    })
    .catch(() => {});
};

window.pollNotifications();
setInterval(window.pollNotifications, 30000);
</script>
@endauth
</body>
</html>
