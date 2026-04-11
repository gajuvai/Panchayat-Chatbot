@extends('layouts.app')
@section('title', 'My Duties')

@section('content')
<div class="space-y-5">

    <div>
        <h1 class="text-xl font-bold text-gray-800">My Duties</h1>
        <p class="text-sm text-gray-500 mt-0.5">Your upcoming duty assignments and open volunteer slots.</p>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif

    {{-- Upcoming Assignments --}}
    <div class="space-y-3">
        <h2 class="text-sm font-semibold text-gray-700">My Upcoming Assignments</h2>

        @forelse($myAssignments as $assignment)
        <div class="bg-white rounded-xl border p-5 hover:shadow-sm transition">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1">
                    <div class="flex items-center gap-2 flex-wrap mb-1">
                        <span class="text-base">{{ $assignment->roster->type_icon }}</span>
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $assignment->status_badge_class }}">
                            {{ ucfirst($assignment->status) }}
                        </span>
                        @if($assignment->is_voluntary)
                        <span class="text-xs text-green-600">Volunteered</span>
                        @endif
                    </div>
                    <h3 class="font-semibold text-gray-800">{{ $assignment->roster->title }}</h3>
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-0.5 mt-1 text-xs text-gray-400">
                        <span>📅 {{ $assignment->roster->roster_date->format('d M Y') }}</span>
                        <span>🕐 {{ $assignment->roster->shift_start }} – {{ $assignment->roster->shift_end }}</span>
                        <span>{{ $assignment->roster->type_label }}</span>
                    </div>
                    @if($assignment->notes)
                    <p class="text-xs text-amber-600 mt-1 bg-amber-50 rounded px-2 py-1">📝 {{ $assignment->notes }}</p>
                    @endif
                </div>

                @if($assignment->status === 'assigned')
                <div class="flex flex-col gap-1.5 flex-shrink-0">
                    <form action="{{ route('duty-roster.confirm', $assignment) }}" method="POST">
                        @csrf @method('PATCH')
                        <button type="submit" class="text-xs bg-green-600 text-white px-3 py-1.5 rounded-lg hover:bg-green-700 transition w-full">Confirm</button>
                    </form>
                    <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'decline-{{ $assignment->id }}' }))"
                        class="text-xs border border-red-200 text-red-500 px-3 py-1.5 rounded-lg hover:bg-red-50 transition">Decline</button>
                </div>
                @elseif($assignment->status === 'confirmed')
                <span class="text-xs text-green-600 font-medium flex-shrink-0">✓ Confirmed</span>
                @endif
            </div>
        </div>

        {{-- Decline Modal --}}
        @if($assignment->status === 'assigned')
        <x-modal name="decline-{{ $assignment->id }}" maxWidth="sm">
            <div class="bg-white rounded-xl overflow-hidden">
                <div class="px-6 py-5">
                    <h3 class="font-semibold text-gray-800 mb-2">Decline Duty</h3>
                    <p class="text-sm text-gray-600 bg-gray-50 rounded-lg px-3 py-2 border">
                        <span class="font-medium">{{ $assignment->roster->title }}</span>
                        — {{ $assignment->roster->roster_date->format('d M Y') }}
                    </p>
                    <p class="text-xs text-gray-400 mt-2">The admin will be notified. Please only decline if necessary.</p>
                </div>
                <div class="flex items-center gap-3 px-6 pb-5">
                    <form action="{{ route('duty-roster.decline', $assignment) }}" method="POST">
                        @csrf @method('PATCH')
                        <button type="submit" class="bg-red-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-red-700 transition">Decline</button>
                    </form>
                    <button @click="show = false" class="text-gray-500 text-sm hover:text-gray-700">Cancel</button>
                </div>
            </div>
        </x-modal>
        @endif

        @empty
        <div class="bg-white rounded-xl border p-8 text-center">
            <div class="text-4xl mb-2">✅</div>
            <p class="text-gray-500 text-sm">No upcoming duty assignments.</p>
        </div>
        @endforelse
    </div>

    {{-- Open Volunteer Slots --}}
    @if($openRosters->isNotEmpty())
    <div class="space-y-3">
        <h2 class="text-sm font-semibold text-gray-700">Open Volunteer Slots</h2>
        <p class="text-xs text-gray-400">Sign up for duties that need volunteers.</p>

        @foreach($openRosters as $roster)
        <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-5">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1">
                        <span>{{ $roster->type_icon }}</span>
                        <span class="text-xs px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700">{{ $roster->type_label }}</span>
                        <span class="text-xs text-indigo-500">
                            {{ $roster->filledSlots() }}/{{ $roster->slots_required }} filled
                        </span>
                    </div>
                    <h3 class="font-semibold text-gray-800">{{ $roster->title }}</h3>
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-0.5 mt-1 text-xs text-indigo-500">
                        <span>📅 {{ $roster->roster_date->format('d M Y') }}</span>
                        <span>🕐 {{ $roster->shift_start }} – {{ $roster->shift_end }}</span>
                    </div>
                    @if($roster->description)
                    <p class="text-xs text-gray-600 mt-1">{{ $roster->description }}</p>
                    @endif
                </div>
                <form action="{{ route('duty-roster.signup', $roster) }}" method="POST" class="flex-shrink-0">
                    @csrf
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                        Sign Up
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Past Duties --}}
    @if($pastDuties->isNotEmpty())
    <div class="space-y-2">
        <h2 class="text-sm font-semibold text-gray-500">Past Duties</h2>
        @foreach($pastDuties as $a)
        <div class="bg-white rounded-lg border px-4 py-3 text-sm flex items-center justify-between gap-4">
            <div>
                <span class="font-medium text-gray-700">{{ $a->roster->title }}</span>
                <span class="text-gray-400 text-xs ml-2">{{ $a->roster->roster_date->format('d M Y') }}</span>
            </div>
            <span class="text-xs px-2 py-0.5 rounded-full {{ $a->status_badge_class }}">{{ ucfirst($a->status) }}</span>
        </div>
        @endforeach
    </div>
    @endif

</div>
@endsection
