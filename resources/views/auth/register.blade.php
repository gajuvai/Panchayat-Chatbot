<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Create Account — {{ config('app.name', 'Panchayat') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50">

<div class="min-h-screen flex">

    {{-- ─── Left panel: branding ─────────────────────────────────── --}}
    <div class="hidden lg:flex lg:w-[45%] bg-gradient-to-br from-indigo-900 via-indigo-800 to-indigo-700 text-white flex-col relative overflow-hidden">

        {{-- Decorative blobs --}}
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
                    Join Your<br>
                    <span class="text-indigo-300">Community Today.</span>
                </h1>
                <p class="text-indigo-200 mt-4 text-base leading-relaxed max-w-sm">
                    Register as a resident to file complaints, join polls, attend events, and stay connected with your neighbourhood.
                </p>

                {{-- Steps --}}
                <div class="mt-8 space-y-4">
                    @foreach([
                        ['1', 'Fill in your details', 'Name, email and password'],
                        ['2', 'Add your flat info',   'Block and flat number (optional)'],
                        ['3', 'Start participating',  'Complaints, polls, events & more'],
                    ] as [$step, $title, $sub])
                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 bg-white/15 rounded-full flex items-center justify-center text-sm font-bold flex-shrink-0 mt-0.5">{{ $step }}</div>
                        <div>
                            <p class="text-sm font-semibold text-white">{{ $title }}</p>
                            <p class="text-xs text-indigo-300 mt-0.5">{{ $sub }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Footer note --}}
            <p class="text-xs text-indigo-400 mt-auto">
                Already a member?
                <a href="{{ route('login') }}" class="text-indigo-200 hover:text-white underline underline-offset-2 transition">Sign in here</a>
            </p>
        </div>
    </div>

    {{-- ─── Right panel: register form ──────────────────────────── --}}
    <div class="flex-1 flex flex-col items-center justify-center p-6 sm:p-10 bg-gray-50 overflow-y-auto">

        {{-- Mobile logo --}}
        <div class="flex lg:hidden items-center gap-3 mb-8">
            <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white font-bold text-lg">P</div>
            <span class="font-bold text-indigo-900 text-xl">Panchayat</span>
        </div>

        <div class="w-full max-w-md">

            {{-- Heading --}}
            <div class="mb-7">
                <h2 class="text-2xl font-bold text-gray-900">Create your account</h2>
                <p class="text-sm text-gray-500 mt-1">Join the Panchayat community platform</p>
            </div>

            {{-- Form --}}
            <form method="POST" action="{{ route('register') }}" class="space-y-4" x-data="{ showPass: false, showConfirm: false }">
                @csrf

                {{-- ── Personal Info ──────────────────────────── --}}
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest">Personal Information</p>

                {{-- Full Name --}}
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">Full name</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <input id="name" name="name" type="text"
                            value="{{ old('name') }}"
                            required autofocus autocomplete="name"
                            placeholder="Ramesh Kumar"
                            class="w-full pl-10 pr-4 py-2.5 border rounded-xl text-sm transition focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                                {{ $errors->has('name') ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-white hover:border-gray-400' }}">
                    </div>
                    @error('name')
                        <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1">
                            <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

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
                            required autocomplete="username"
                            placeholder="you@example.com"
                            class="w-full pl-10 pr-4 py-2.5 border rounded-xl text-sm transition focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                                {{ $errors->has('email') ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-white hover:border-gray-400' }}">
                    </div>
                    @error('email')
                        <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1">
                            <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Phone --}}
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Phone number
                        <span class="text-gray-400 font-normal">(optional)</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                        </div>
                        <input id="phone" name="phone" type="tel"
                            value="{{ old('phone') }}"
                            autocomplete="tel"
                            placeholder="+91 98765 43210"
                            class="w-full pl-10 pr-4 py-2.5 border rounded-xl text-sm transition focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                                {{ $errors->has('phone') ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-white hover:border-gray-400' }}">
                    </div>
                    @error('phone')
                        <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1">
                            <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- ── Residence Info ──────────────────────────── --}}
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest pt-1">Residence Details <span class="normal-case font-normal text-gray-400">(optional)</span></p>

                {{-- Block + Flat in a row --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="block" class="block text-sm font-medium text-gray-700 mb-1.5">Block</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                            <input id="block" name="block" type="text"
                                value="{{ old('block') }}"
                                placeholder="A, B, C…"
                                class="w-full pl-10 pr-3 py-2.5 border rounded-xl text-sm transition focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                                    {{ $errors->has('block') ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-white hover:border-gray-400' }}">
                        </div>
                        @error('block')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="flat_number" class="block text-sm font-medium text-gray-700 mb-1.5">Flat No.</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                            </div>
                            <input id="flat_number" name="flat_number" type="text"
                                value="{{ old('flat_number') }}"
                                placeholder="101, 202…"
                                class="w-full pl-10 pr-3 py-2.5 border rounded-xl text-sm transition focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                                    {{ $errors->has('flat_number') ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-white hover:border-gray-400' }}">
                        </div>
                        @error('flat_number')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- ── Security ────────────────────────────────── --}}
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest pt-1">Password</p>

                {{-- Password --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input id="password" name="password"
                            :type="showPass ? 'text' : 'password'"
                            required autocomplete="new-password"
                            placeholder="Min. 8 characters"
                            class="w-full pl-10 pr-11 py-2.5 border rounded-xl text-sm transition focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                                {{ $errors->has('password') ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-white hover:border-gray-400' }}">
                        <button type="button" @click="showPass = !showPass"
                            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600 transition">
                            <template x-if="!showPass">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </template>
                            <template x-if="showPass">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </template>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1">
                            <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Confirm Password --}}
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1.5">Confirm password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <input id="password_confirmation" name="password_confirmation"
                            :type="showConfirm ? 'text' : 'password'"
                            required autocomplete="new-password"
                            placeholder="Re-enter password"
                            class="w-full pl-10 pr-11 py-2.5 border rounded-xl text-sm transition focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                                {{ $errors->has('password_confirmation') ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-white hover:border-gray-400' }}">
                        <button type="button" @click="showConfirm = !showConfirm"
                            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600 transition">
                            <template x-if="!showConfirm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </template>
                            <template x-if="showConfirm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </template>
                        </button>
                    </div>
                    @error('password_confirmation')
                        <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1">
                            <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Submit --}}
                <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-semibold py-2.5 px-4 rounded-xl transition duration-150 shadow-sm shadow-indigo-200 flex items-center justify-center gap-2 text-sm mt-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    Create my account
                </button>

            </form>

            {{-- Sign in link --}}
            <p class="text-center text-sm text-gray-500 mt-6">
                Already have an account?
                <a href="{{ route('login') }}" class="text-indigo-600 font-medium hover:underline">Sign in</a>
            </p>

        </div>
    </div>
</div>

</body>
</html>
