@extends('layouts.app')
@section('title', 'Rule Book')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">{{ $rules->count() }} section(s)</p>
        <a href="{{ route('admin.rules.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
            + New Section
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-600 w-12">#</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Title</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Excerpt</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Author</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($rules as $rule)
                <tr class="border-b hover:bg-gray-50 transition">
                    <td class="px-4 py-3 text-gray-400 font-mono text-xs text-center">{{ $rule->section_order }}</td>
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $rule->title }}</td>
                    <td class="px-4 py-3 text-gray-500 text-xs max-w-xs">
                        {{ Str::limit(strip_tags($rule->content), 80) }}
                    </td>
                    <td class="px-4 py-3">
                        @if($rule->is_published)
                            <span class="text-xs px-2 py-0.5 rounded bg-green-100 text-green-700">Published</span>
                        @else
                            <span class="text-xs px-2 py-0.5 rounded bg-gray-100 text-gray-500">Draft</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $rule->author?->name ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('admin.rules.show', $rule) }}" class="text-indigo-600 hover:underline text-xs">View</a>
                            <a href="{{ route('admin.rules.edit', $rule) }}" class="text-gray-500 hover:underline text-xs">Edit</a>
                            <form action="{{ route('admin.rules.destroy', $rule) }}" method="POST"
                                onsubmit="return confirm('Delete this section?')">
                                @csrf @method('DELETE')
                                <button class="text-red-400 hover:underline text-xs">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">No sections yet. Create the first one.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
