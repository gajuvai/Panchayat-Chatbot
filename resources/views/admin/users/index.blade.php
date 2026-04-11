@extends('layouts.app')
@section('title', 'Manage Users')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">{{ $users->total() }} user(s)</p>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif

    {{-- Search / Filter --}}
    <form method="GET" class="bg-white rounded-xl border p-4 flex flex-wrap gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or email..."
            class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm w-56">
        <select name="role" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
            <option value="">All Roles</option>
            <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
            <option value="security_head" {{ request('role') === 'security_head' ? 'selected' : '' }}>Security Head</option>
            <option value="resident" {{ request('role') === 'resident' ? 'selected' : '' }}>Resident</option>
        </select>
        <button type="submit" class="bg-indigo-600 text-white px-4 py-1.5 rounded-lg text-sm hover:bg-indigo-700">Filter</button>
        <a href="{{ route('admin.users.index') }}" class="text-gray-500 text-sm py-1.5 hover:text-gray-700">Clear</a>
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-xl border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Name</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Email</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Role</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Flat</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Complaints</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Joined</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr class="border-b hover:bg-gray-50 transition">
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $user->name }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $user->email }}</td>
                    <td class="px-4 py-3">
                        @php
                            $roleName = $user->role?->name ?? 'resident';
                            $badgeClass = match($roleName) {
                                'admin'         => 'bg-red-100 text-red-700',
                                'security_head' => 'bg-blue-100 text-blue-700',
                                default         => 'bg-green-100 text-green-700',
                            };
                        @endphp
                        <span class="text-xs px-2 py-0.5 rounded {{ $badgeClass }}">
                            {{ $user->role?->display_name ?? ucfirst($roleName) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">
                        {{ $user->block ? $user->block . '-' . $user->flat_number : ($user->flat_number ?? '—') }}
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs text-center">{{ $user->complaints_count }}</td>
                    <td class="px-4 py-3 text-gray-400 text-xs">{{ $user->created_at->format('d M Y') }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('admin.users.show', $user) }}" class="text-indigo-600 hover:underline text-xs">View</a>
                            <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'edit-user-{{ $user->id }}' }))"
                                class="text-gray-500 hover:underline text-xs">Edit Role</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $users->links() }}
</div>

{{-- Edit Role Modals per user --}}
@foreach($users as $user)
@php $isActiveEdit = old('_edit_id') == $user->id && $errors->any(); @endphp

<x-modal name="edit-user-{{ $user->id }}" :show="old('_edit_id') == $user->id && $errors->any()" maxWidth="md">
    <div class="bg-white rounded-xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b bg-gray-50">
            <div>
                <h2 class="text-base font-semibold text-gray-800">Edit User Role</h2>
                <p class="text-xs text-gray-500 mt-0.5">Only the role can be changed.</p>
            </div>
            <button @click="show = false" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form action="{{ route('admin.users.update', $user) }}" method="POST" class="p-6 space-y-4">
            @csrf @method('PATCH')
            <input type="hidden" name="_edit_id" value="{{ $user->id }}">
            <div class="space-y-2 pb-4 border-b">
                <div>
                    <span class="text-xs font-medium text-gray-400">Name</span>
                    <p class="text-sm text-gray-700 mt-0.5">{{ $user->name }}</p>
                </div>
                <div>
                    <span class="text-xs font-medium text-gray-400">Email</span>
                    <p class="text-sm text-gray-700 mt-0.5">{{ $user->email }}</p>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                <select name="role_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('role_id') border-red-400 @enderror">
                    @foreach($roles as $role)
                    <option value="{{ $role->id }}" {{ ($isActiveEdit ? old('role_id') : $user->role_id) == $role->id ? 'selected' : '' }}>
                        {{ $role->display_name }}
                    </option>
                    @endforeach
                </select>
                @error('role_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="flex gap-3 pt-2 border-t">
                <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                    Save Changes
                </button>
                <button type="button" @click="show = false" class="text-gray-500 text-sm py-2 hover:text-gray-700">Cancel</button>
            </div>
        </form>
    </div>
</x-modal>

@endforeach
@endsection
