@extends('layouts.app')
@section('title', 'Maintenance Requests')

@section('content')
<div class="space-y-4">

    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">{{ $requests->total() }} request(s) found</p>
        <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'create-maintenance' }))"
            class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Request
        </button>
    </div>

    {{-- Filter --}}
    <form method="GET" class="bg-white rounded-xl border p-4 flex flex-wrap gap-3">
        <select name="status" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
            <option value="">All Statuses</option>
            @foreach(\App\Enums\MaintenanceStatus::cases() as $s)
            <option value="{{ $s->value }}" {{ request('status') === $s->value ? 'selected' : '' }}>{{ $s->label() }}</option>
            @endforeach
        </select>
        <button type="submit" class="bg-gray-100 text-gray-700 px-4 py-1.5 rounded-lg text-sm hover:bg-gray-200">Filter</button>
        <a href="{{ route('resident.maintenance.index') }}" class="text-gray-500 text-sm py-1.5 hover:text-gray-700">Clear</a>
    </form>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif

    {{-- Request Cards --}}
    @forelse($requests as $req)
    <div class="bg-white rounded-xl border p-5 hover:shadow-sm transition">
        <div class="flex items-start justify-between gap-4">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap mb-1">
                    <span class="font-mono text-xs font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">
                        {{ $req->request_number }}
                    </span>
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $req->status->badgeClass() }}">
                        {{ $req->status->label() }}
                    </span>
                    @php
                        $priorityClass = match($req->priority) {
                            'urgent' => 'bg-red-100 text-red-700',
                            'high'   => 'bg-orange-100 text-orange-700',
                            'medium' => 'bg-yellow-100 text-yellow-700',
                            default  => 'bg-gray-100 text-gray-500',
                        };
                    @endphp
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $priorityClass }}">{{ ucfirst($req->priority) }}</span>
                </div>
                <h3 class="font-semibold text-gray-800">{{ $req->title }}</h3>
                <p class="text-sm text-gray-500 mt-0.5 line-clamp-2">{{ $req->description }}</p>
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-2 text-xs text-gray-400">
                    @if($req->category) <span>{{ $req->category->name }}</span>@endif
                    @if($req->location) <span>📍 {{ $req->location }}</span>@endif
                    <span>{{ $req->created_at->diffForHumans() }}</span>
                    @if($req->assignedTo) <span>Assigned to {{ $req->assignedTo->name }}</span>@endif
                    @if($req->scheduled_at) <span>Scheduled: {{ $req->scheduled_at->format('d M Y, h:i A') }}</span>@endif
                </div>
                @if($req->rejection_reason)
                <p class="text-xs text-red-600 mt-1 bg-red-50 rounded px-2 py-1">Reason: {{ $req->rejection_reason }}</p>
                @endif
                @if($req->completion_notes)
                <p class="text-xs text-green-600 mt-1 bg-green-50 rounded px-2 py-1">✓ {{ $req->completion_notes }}</p>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="bg-white rounded-xl border p-12 text-center">
        <div class="text-5xl mb-3">🔧</div>
        <p class="text-gray-500 text-sm">No maintenance requests yet.</p>
        <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'create-maintenance' }))"
            class="mt-3 inline-block text-indigo-600 text-sm hover:underline">Submit your first request</button>
    </div>
    @endforelse

    {{ $requests->links() }}
</div>

{{-- Create Maintenance Request Modal --}}
<x-modal name="create-maintenance" :show="$errors->any()" maxWidth="xl">
    <div class="bg-white rounded-xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b bg-gray-50">
            <div>
                <h2 class="text-base font-semibold text-gray-800">New Maintenance Request</h2>
                <p class="text-xs text-gray-500 mt-0.5">Report a maintenance issue to the management.</p>
            </div>
            <button @click="show = false" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="overflow-y-auto max-h-[80vh]">
        <form action="{{ route('resident.maintenance.store') }}" method="POST" class="p-6 space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('title') border-red-400 @enderror"
                    placeholder="e.g. Broken lift, Water leakage in corridor">
                @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description <span class="text-red-500">*</span></label>
                <textarea name="description" rows="4"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('description') border-red-400 @enderror"
                    placeholder="Describe the issue in detail — when it started, exact location, impact...">{{ old('description') }}</textarea>
                @error('description')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select name="category_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">— Select —</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Priority <span class="text-red-500">*</span></label>
                    <select name="priority" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('priority') border-red-400 @enderror">
                        @foreach(['low','medium','high','urgent'] as $p)
                        <option value="{{ $p }}" {{ old('priority', 'medium') === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Location <span class="text-gray-400 font-normal">(optional)</span></label>
                <input type="text" name="location" value="{{ old('location') }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="e.g. Block B, 3rd floor corridor, Near parking">
            </div>

            <div class="flex gap-3 pt-2 border-t">
                <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                    Submit Request
                </button>
                <button type="button" @click="show = false" class="text-gray-500 text-sm py-2 hover:text-gray-700">Cancel</button>
            </div>
        </form>
        </div>
    </div>
</x-modal>
@endsection
