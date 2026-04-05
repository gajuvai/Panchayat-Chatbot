<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panchayat Chatbot - Community Management Platform</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-indigo-50 to-white font-sans antialiased">

{{-- Hero --}}
<div class="min-h-screen flex flex-col">
    <header class="py-6 px-8 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-indigo-600 rounded-full flex items-center justify-center text-white font-bold text-lg">P</div>
            <span class="font-bold text-indigo-900 text-xl">Panchayat</span>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('login') }}" class="text-indigo-700 text-sm font-medium hover:text-indigo-900">Sign In</a>
            <a href="{{ route('register') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">Register</a>
        </div>
    </header>

    <main class="flex-1 flex flex-col items-center justify-center text-center px-6 py-16">
        <div class="max-w-3xl">
            <span class="bg-indigo-100 text-indigo-700 text-xs font-medium px-3 py-1 rounded-full">Digital Community Platform</span>
            <h1 class="text-5xl font-bold text-indigo-900 mt-6 leading-tight">
                Your Society, <span class="text-indigo-600">Managed Smarter</span>
            </h1>
            <p class="text-lg text-gray-600 mt-4 max-w-xl mx-auto">
                Panchayat connects residents with admins and security teams. File complaints, track resolution, vote on community decisions — all in one place.
            </p>
            <div class="flex items-center justify-center gap-4 mt-8">
                <a href="{{ route('register') }}" class="bg-indigo-600 text-white px-8 py-3 rounded-xl font-semibold hover:bg-indigo-700 transition shadow-lg shadow-indigo-200">
                    Get Started Free
                </a>
                <a href="{{ route('login') }}" class="border-2 border-indigo-200 text-indigo-700 px-8 py-3 rounded-xl font-semibold hover:border-indigo-400 transition">
                    Sign In
                </a>
            </div>
        </div>

        {{-- Feature cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mt-20 max-w-4xl w-full">
            @foreach([
                ['icon'=>'📋','title'=>'File Complaints','desc'=>'Voice-record or type complaints. Track status in real-time.'],
                ['icon'=>'🗳','title'=>'Community Votes','desc'=>'Participate in polls and surveys on society decisions.'],
                ['icon'=>'🚨','title'=>'Emergency Alerts','desc'=>'Instant emergency notifications to security and residents.'],
                ['icon'=>'🤖','title'=>'AI Chatbot','desc'=>'24/7 assistant for queries, complaint tracking, and FAQs.'],
                ['icon'=>'📅','title'=>'Events & Meetings','desc'=>'Stay updated on community events with RSVP support.'],
                ['icon'=>'📊','title'=>'Admin Analytics','desc'=>'Data-driven insights on community engagement and issues.'],
            ] as $feat)
            <div class="bg-white rounded-2xl border p-5 text-left hover:shadow-md transition">
                <div class="text-3xl mb-3">{{ $feat['icon'] }}</div>
                <h3 class="font-semibold text-gray-800">{{ $feat['title'] }}</h3>
                <p class="text-sm text-gray-500 mt-1">{{ $feat['desc'] }}</p>
            </div>
            @endforeach
        </div>

        {{-- Demo credentials --}}
        <div class="mt-16 bg-white rounded-2xl border p-6 max-w-lg w-full">
            <h3 class="font-semibold text-gray-800 mb-3">Demo Credentials</h3>
            <div class="space-y-2 text-sm">
                @foreach([
                    ['role'=>'Admin','email'=>'admin@panchayat.local'],
                    ['role'=>'Security Head','email'=>'security@panchayat.local'],
                    ['role'=>'Resident','email'=>'ramesh@example.com'],
                ] as $demo)
                <div class="flex items-center justify-between bg-gray-50 rounded-lg px-3 py-2">
                    <span class="font-medium text-gray-700">{{ $demo['role'] }}</span>
                    <span class="text-gray-500">{{ $demo['email'] }} / <code class="bg-gray-200 px-1 rounded text-xs">password</code></span>
                </div>
                @endforeach
            </div>
        </div>
    </main>
</div>

</body>
</html>
