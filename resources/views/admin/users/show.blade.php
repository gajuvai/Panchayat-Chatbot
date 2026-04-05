@extends('layouts.app')
@section('title', $user->name)

@section('content')
<div class="max-w-2xl mx-auto space-y-4">
    <a href="{{ route('admin.users.index') }}" class="text-indigo-600 text-sm hover:underline inline-block">← Back to Users</a>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif

    {{-- Profile Card --}}
    <div class="bg-white rounded-xl border p-6">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ $user->name }}</h1>
                <p class="text-gray-500 text-sm mt-0.5">{{ $user->email }}</p>
            </div>
            @php
                $roleName = $user->role?->name ?? 'resident';
                $badgeClass = match($roleName) {
                    'admin'         => 'bg-red-100 text-red-700',
                    'security_head' => 'bg-blue-100 text-blue-700',
                    default         => 'bg-green-100 text-green-700',
                };
            @endphp
            <span class="text-xs px-3 py-1 rounded-full font-medium {{ $badgeClass }}">
                {{ $user->role?->display_name ?? ucfirst($roleName) }}
            </span>
        </div>

        <div class="grid grid-cols-2 gap-4 border-t pt-4 text-sm">
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Flat / Block</p>
                <p class="text-gray-700 font-medium">
                    {{ $user->block ? $user->block . '-' . $user->flat_number : ($user->flat_number ?? '—') }}
                </p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Phone</p>
                <p class="text-gray-700 font-medium">{{ $user->phone ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Status</p>
                <p class="font-medium {{ $user->is_active ? 'text-green-600' : 'text-red-500' }}">
                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                </p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Joined</p>
                <p class="text-gray-700 font-medium">{{ $user->created_at->format('d M Y') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Total Complaints</p>
                <p class="text-gray-700 font-medium">{{ $complaintsCount }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Email Verified</p>
                <p class="text-gray-700 font-medium">
                    {{ $user->email_verified_at ? $user->email_verified_at->format('d M Y') : 'Not verified' }}
                </p>
            </div>
        </div>

        <div class="mt-5">
            <a href="{{ route('admin.users.edit', $user) }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
                Edit Role
            </a>
        </div>
    </div>

    {{-- Recent Complaints (residents) --}}
    @if($recentComplaints->isNotEmpty())
    <div class="bg-white rounded-xl border overflow-hidden">
        <div class="px-4 py-3 border-b bg-gray-50">
            <h2 class="text-sm font-semibold text-gray-700">Recent Complaints</h2>
        </div>
        <table class="w-full text-sm">
            <thead class="border-b">
                <tr>
                    <th class="text-left px-4 py-2 font-medium text-gray-600 text-xs">#</th>
                    <th class="text-left px-4 py-2 font-medium text-gray-600 text-xs">Title</th>
                    <th class="text-left px-4 py-2 font-medium text-gray-600 text-xs">Status</th>
                    <th class="text-left px-4 py-2 font-medium text-gray-600 text-xs">Date</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentComplaints as $complaint)
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-4 py-2 text-xs text-gray-400 font-mono">{{ $complaint->complaint_number }}</td>
                    <td class="px-4 py-2 text-gray-700">{{ Str::limit($complaint->title, 45) }}</td>
                    <td class="px-4 py-2">
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $complaint->status->badgeClass() }}">
                            {{ $complaint->status->label() }}
                        </span>
                    </td>
                    <td class="px-4 py-2 text-xs text-gray-400">{{ $complaint->created_at->format('d M Y') }}</td>
                    <td class="px-4 py-2">
                        <a href="{{ route('admin.complaints.show', $complaint) }}" class="text-indigo-600 hover:underline text-xs">View</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection
