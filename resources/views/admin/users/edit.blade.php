@extends('layouts.app')
@section('title', 'Edit User — ' . $user->name)

@section('content')
<div class="max-w-lg mx-auto">
    <a href="{{ route('admin.users.show', $user) }}" class="text-indigo-600 text-sm hover:underline mb-4 inline-block">← Back</a>

    <div class="bg-white rounded-xl border p-6">
        <h1 class="text-xl font-bold text-gray-800 mb-1">Edit User Role</h1>
        <p class="text-sm text-gray-500 mb-6">Only the role can be changed. All other details are read-only.</p>

        {{-- Read-only info --}}
        <div class="space-y-3 mb-6 pb-6 border-b">
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1">Name</label>
                <p class="text-sm text-gray-700 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2">{{ $user->name }}</p>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1">Email</label>
                <p class="text-sm text-gray-700 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2">{{ $user->email }}</p>
            </div>
        </div>

        <form action="{{ route('admin.users.update', $user) }}" method="POST" class="space-y-4">
            @csrf @method('PATCH')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                <select name="role_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm @error('role_id') border-red-400 @enderror">
                    @foreach($roles as $role)
                    <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                        {{ $role->display_name }}
                    </option>
                    @endforeach
                </select>
                @error('role_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
                    Save Changes
                </button>
                <a href="{{ route('admin.users.show', $user) }}" class="text-gray-500 text-sm py-2 hover:text-gray-700">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
