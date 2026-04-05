<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign In — {{ config('app.name', 'Panchayat') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50">

<div class="min-h-screen flex">

    {{-- ─── Left panel: branding ─────────────────────────────────── --}}
    <div class="hidden lg:flex lg:w-[55%] bg-gradient-to-br from-indigo-900 via-indigo-800 to-indigo-700 text-white flex-col relative overflow-hidden">

        {{-- Decorative circles --}}
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-indigo-600 rounded-full opacity-30 blur-3xl"></div>
        <div class="absolute bottom-10 right-0 w-80 h-80 bg-indigo-500 rounded-full opacity-20 blur-3xl"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-indigo-800 rounded-full opacity-20 blur-3xl"></div>

        <div class="relative z-10 flex flex-col h-full p-12">

            {{-- Logo --}}
            <div class="flex items-center gap-3 mb-auto">
                <div class="w-11 h-11 bg-white/20 backdrop-blur rounded-xl flex items-center justify-center font-bold text-xl">P</div>
                <div>
                    <div class="font-bold text-lg leading-tight">Panchayat</div>
                    <div class="text-indigo-300 text-xs">Community Platform</div>
                </div>
            </div>

            {{-- Headline --}}
            <div class="my-auto">
                <h1 class="text-4xl font-bold leading-snug">
                    Your Society,<br>
                    <span class="text-indigo-300">Managed Smarter.</span>
                </h1>
                <p class="text-indigo-200 mt-4 text-base leading-relaxed max-w-sm">
                    One platform for residents, admins and security — complaints, events, polls, alerts and more.
                </p>

                {{-- Feature list --}}
                <ul class="mt-8 space-y-3">
                    @foreach([
                        ['📋', 'File & track complaints in real-time'],
                        ['🗳',  'Vote on community polls & decisions'],
                        ['🚨', 'Instant emergency alerts & notifications'],
                        ['🤖', '24/7 AI chatbot for queries & status'],
                        ['📅', 'Community events with RSVP support'],
                    ] as [$icon, $text])
                    <li class="flex items-center gap-3 text-sm text-indigo-100">
                        <span class="w-8 h-8 bg-white/10 rounded-lg flex items-center justify-center text-base flex-shrink-0">{{ $icon }}</span>
                        {{ $text }}
                    </li>
                    @endforeach
                </ul>
            </div>

        </div>
    </div>

    {{-- ─── Right panel: login form ──────────────────────────────── --}}
    <div class="flex-1 flex flex-col items-center justify-center p-6 sm:p-10 bg-gray-50">

        {{-- Mobile logo --}}
        <div class="flex lg:hidden items-center gap-3 mb-8">
            <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white font-bold text-lg">P</div>
            <span class="font-bold text-indigo-900 text-xl">Panchayat</span>
        </div>

        <div class="w-full max-w-md">

            {{-- Heading --}}
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-gray-900">Welcome back</h2>
                <p class="text-sm text-gray-500 mt-1">Sign in to your community account</p>
            </div>

            {{-- Session status --}}
            @if(session('status'))
                <div class="bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-xl mb-5">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Form --}}
            <form method="POST" action="{{ route('login') }}" id="loginForm" class="space-y-5">
                @csrf

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email address</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                            </svg>
                        </div>
                        <input id="email" name="email" type="email"
                            value="{{ old('email') }}"
                            required autofocus autocomplete="username"
                            placeholder="you@example.com"
                            class="w-full pl-10 pr-4 py-2.5 border rounded-xl text-sm transition focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                                {{ $errors->has('email') ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-white hover:border-gray-400' }}">
                    </div>
                    @error('email')
                        <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Password --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        @if(Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-xs text-indigo-600 hover:text-indigo-800 hover:underline">
                                Forgot password?
                            </a>
                        @endif
                    </div>
                    <div class="relative" x-data="{ show: false }">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input id="password" name="password"
                            :type="show ? 'text' : 'password'"
                            required autocomplete="current-password"
                            placeholder="••••••••"
                            class="w-full pl-10 pr-11 py-2.5 border rounded-xl text-sm transition focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                                {{ $errors->has('password') ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-white hover:border-gray-400' }}">
                        {{-- Toggle visibility --}}
                        <button type="button" @click="show = !show"
                            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600 transition">
                            <template x-if="!show">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </template>
                            <template x-if="show">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </template>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Remember me --}}
                <div class="flex items-center">
                    <input id="remember_me" name="remember" type="checkbox"
                        class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                    <label for="remember_me" class="ml-2 text-sm text-gray-600 cursor-pointer select-none">
                        Keep me signed in
                    </label>
                </div>

                {{-- Submit --}}
                <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-semibold py-2.5 px-4 rounded-xl transition duration-150 shadow-sm shadow-indigo-200 flex items-center justify-center gap-2 text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                    Sign in to Panchayat
                </button>
            </form>

            {{-- Mobile demo credentials --}}
            <div class="mt-8 lg:hidden">
                <p class="text-xs text-gray-400 font-semibold uppercase tracking-widest mb-3 text-center">Quick demo access</p>
                <div class="space-y-2">
                    @foreach([
                        ['Admin',         'admin@panchayat.local',    'bg-yellow-50 border-yellow-200 text-yellow-800'],
                        ['Security Head', 'security@panchayat.local', 'bg-red-50 border-red-200 text-red-800'],
                        ['Resident',      'ramesh@example.com',        'bg-green-50 border-green-200 text-green-800'],
                    ] as [$role, $email, $cls])
                    <button type="button" onclick="quickFill('{{ $email }}')"
                        class="w-full flex items-center justify-between px-4 py-2.5 rounded-xl border {{ $cls }} text-sm hover:shadow-sm transition text-left">
                        <span class="font-semibold">{{ $role }}</span>
                        <span class="text-xs opacity-70">{{ $email }}</span>
                    </button>
                    @endforeach
                </div>
                <p class="text-center text-xs text-gray-400 mt-2">Password: <code class="bg-gray-100 px-1 rounded">password</code></p>
            </div>

            {{-- Register link --}}
            <p class="text-center text-sm text-gray-500 mt-8">
                New to Panchayat?
                <a href="{{ route('register') }}" class="text-indigo-600 font-medium hover:underline">Create an account</a>
            </p>
        </div>
    </div>
</div>

<script>
function quickFill(email) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = 'password';
    document.getElementById('email').dispatchEvent(new Event('input'));
    document.getElementById('password').focus();
}
</script>

</body>
</html>
