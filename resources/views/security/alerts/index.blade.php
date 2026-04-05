@extends('layouts.app')
@section('title', 'Emergency Alerts')

@section('content')
<div class="space-y-4" x-data="{ showForm: {{ $errors->any() ? 'true' : 'false' }} }">

    {{-- Page header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Emergency Alerts</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ $alerts->total() }} alert(s) total</p>
        </div>
        <button @click="showForm = !showForm"
            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            Trigger Alert
        </button>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">
        {{ session('success') }}
    </div>
    @endif

    {{-- Inline create form --}}
    <div x-show="showForm" x-transition x-cloak>
        <div class="bg-red-50 border-2 border-red-300 rounded-xl p-6">
            <div class="flex items-start gap-3 mb-5">
                <div class="flex-shrink-0 w-10 h-10 bg-red-600 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-red-800">Trigger Emergency Alert</h2>
                    <p class="text-sm text-red-600 mt-0.5">
                        Warning: This will immediately notify all residents and relevant authorities.
                        Only trigger an alert for genuine emergencies.
                    </p>
                </div>
            </div>

            <form action="{{ route('security.alerts.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-semibold text-red-800 mb-1">Alert Type <span class="text-red-600">*</span></label>
                    <select name="alert_type"
                        class="w-full border border-red-300 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-red-400 focus:outline-none @error('alert_type') border-red-500 @enderror">
                        <option value="">— Select Type —</option>
                        @foreach(['fire' => 'Fire', 'medical' => 'Medical Emergency', 'security' => 'Security Threat', 'natural_disaster' => 'Natural Disaster', 'other' => 'Other'] as $val => $label)
                        <option value="{{ $val }}" {{ old('alert_type') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('alert_type')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-red-800 mb-1">Message / Details <span class="text-red-600">*</span></label>
                    <textarea name="message" rows="4"
                        placeholder="Describe the emergency clearly — include location, nature of threat, and any immediate actions residents should take..."
                        class="w-full border border-red-300 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-red-400 focus:outline-none @error('message') border-red-500 @enderror">{{ old('message') }}</textarea>
                    @error('message')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="flex gap-3 pt-1">
                    <button type="submit"
                        class="bg-red-600 hover:bg-red-700 text-white px-6 py-2.5 rounded-lg text-sm font-bold tracking-wide uppercase transition"
                        onclick="return confirm('Are you sure? This will trigger an emergency alert for all residents.')">
                        Trigger Emergency Alert
                    </button>
                    <button type="button" @click="showForm = false"
                        class="text-gray-500 text-sm py-2.5 hover:text-gray-700">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Alerts table --}}
    <div class="bg-white rounded-xl border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Alert Type</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Message</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Triggered By</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Date</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($alerts as $alert)
                <tr class="border-b hover:bg-gray-50 transition">
                    <td class="px-4 py-3">
                        <span class="text-xs px-2 py-0.5 rounded font-medium {{ $alert->alertTypeBadgeClass() }}">
                            {{ ucfirst(str_replace('_', ' ', $alert->alert_type)) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-700 max-w-xs">
                        <p class="truncate">{{ $alert->message }}</p>
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $alert->triggeredBy?->name ?? 'Unknown' }}</td>
                    <td class="px-4 py-3">
                        @if($alert->is_active)
                            <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded bg-red-100 text-red-700 font-medium">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse inline-block"></span>
                                Active
                            </span>
                        @else
                            <span class="text-xs px-2 py-0.5 rounded bg-green-100 text-green-700 font-medium">Resolved</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-400 text-xs">{{ $alert->created_at->format('d M Y, h:i A') }}</td>
                    <td class="px-4 py-3">
                        <a href="{{ route('security.alerts.show', $alert) }}"
                            class="text-indigo-600 hover:underline text-xs font-medium">View</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center text-gray-400">No emergency alerts recorded.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $alerts->links() }}

</div>
@endsection
