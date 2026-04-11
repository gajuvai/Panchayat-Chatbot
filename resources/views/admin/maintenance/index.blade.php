@extends('layouts.app')
@section('title', 'Maintenance Requests')

@section('content')
<div class="space-y-4">

    {{-- Stats row --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-center">
            <div class="text-2xl font-bold text-yellow-700">{{ $counts['pending'] }}</div>
            <div class="text-xs text-yellow-600 mt-0.5">Pending Review</div>
        </div>
        <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-4 text-center">
            <div class="text-2xl font-bold text-indigo-700">{{ $counts['scheduled'] }}</div>
            <div class="text-xs text-indigo-600 mt-0.5">Scheduled</div>
        </div>
        <div class="bg-orange-50 border border-orange-200 rounded-xl p-4 text-center">
            <div class="text-2xl font-bold text-orange-700">{{ $counts['in_progress'] }}</div>
            <div class="text-xs text-orange-600 mt-0.5">In Progress</div>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="bg-white rounded-xl border p-4 flex flex-wrap gap-3">
        <input type="text" name="search" value="{{ request('search') }}"
            placeholder="Search title, number, resident..."
            class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm w-60 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <select name="status" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
            <option value="">All Statuses</option>
            @foreach(\App\Enums\MaintenanceStatus::cases() as $s)
            <option value="{{ $s->value }}" {{ request('status') === $s->value ? 'selected' : '' }}>{{ $s->label() }}</option>
            @endforeach
        </select>
        <select name="priority" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
            <option value="">All Priorities</option>
            @foreach(['urgent','high','medium','low'] as $p)
            <option value="{{ $p }}" {{ request('priority') === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
            @endforeach
        </select>
        <button type="submit" class="bg-indigo-600 text-white px-4 py-1.5 rounded-lg text-sm hover:bg-indigo-700">Filter</button>
        <a href="{{ route('admin.maintenance.index') }}" class="text-gray-500 text-sm py-1.5 hover:text-gray-700">Clear</a>
    </form>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif

    {{-- Table --}}
    <div class="bg-white rounded-xl border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Request</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Resident</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Priority</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Assigned To</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Date</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $req)
                <tr class="border-b hover:bg-gray-50 transition">
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-800">{{ $req->title }}</p>
                        <p class="font-mono text-xs text-indigo-600">{{ $req->request_number }}</p>
                    </td>
                    <td class="px-4 py-3 text-gray-600 text-sm">
                        {{ $req->requestedBy->name }}
                        @if($req->requestedBy->flat_number)
                        <br><span class="text-xs text-gray-400">Flat {{ $req->requestedBy->block }}-{{ $req->requestedBy->flat_number }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @php
                            $pc = match($req->priority) {
                                'urgent' => 'bg-red-100 text-red-700',
                                'high'   => 'bg-orange-100 text-orange-700',
                                'medium' => 'bg-yellow-100 text-yellow-700',
                                default  => 'bg-gray-100 text-gray-500',
                            };
                        @endphp
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $pc }}">{{ ucfirst($req->priority) }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $req->status->badgeClass() }}">{{ $req->status->label() }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $req->assignedTo?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-400 text-xs">{{ $req->created_at->format('d M Y') }}</td>
                    <td class="px-4 py-3">
                        <a href="{{ route('admin.maintenance.show', $req) }}" class="text-indigo-600 hover:underline text-xs font-medium">View →</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">No maintenance requests found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $requests->links() }}
</div>
@endsection
