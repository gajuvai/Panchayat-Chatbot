@extends('layouts.app')
@section('title', 'Document Library — Admin')

@section('content')
<div class="space-y-4">

    <div class="flex items-center justify-between flex-wrap gap-3">
        <h1 class="text-xl font-bold text-gray-800">Document Library</h1>
        <div class="flex items-center gap-2">
            <a href="{{ route('documents.browse') }}" class="border border-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm hover:bg-gray-50 transition">Browse View</a>
            <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'add-category' }))"
                class="border border-indigo-300 text-indigo-600 px-4 py-2 rounded-lg text-sm hover:bg-indigo-50 transition">+ Category</button>
            <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'upload-document' }))"
                class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                Upload Document
            </button>
        </div>
    </div>

    {{-- Category chips --}}
    @if($categories->isNotEmpty())
    <div class="flex flex-wrap gap-2 bg-white rounded-xl border p-4">
        @foreach($categories as $cat)
        <div class="flex items-center gap-1.5 text-xs bg-gray-50 border rounded-full px-3 py-1.5">
            <span>{{ $cat->icon }}</span>
            <span class="font-medium">{{ $cat->name }}</span>
            <span class="text-gray-400">({{ $cat->documents_count }})</span>
            @if($cat->documents_count === 0)
            <form action="{{ route('admin.document-categories.destroy', $cat) }}" method="POST" class="inline">
                @csrf @method('DELETE')
                <button type="submit" class="text-red-300 hover:text-red-500 ml-1" title="Delete category">×</button>
            </form>
            @endif
        </div>
        @endforeach
    </div>
    @endif

    {{-- Filters --}}
    <form method="GET" class="bg-white rounded-xl border p-4 flex flex-wrap gap-3">
        <input type="text" name="search" value="{{ request('search') }}"
            placeholder="Search title, filename..."
            class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm w-56 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <select name="category" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
            <option value="">All Categories</option>
            @foreach($categories as $cat)
            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="bg-indigo-600 text-white px-4 py-1.5 rounded-lg text-sm hover:bg-indigo-700">Filter</button>
        <a href="{{ route('admin.documents.index') }}" class="text-gray-500 text-sm py-1.5 hover:text-gray-700">Clear</a>
    </form>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-xl border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Document</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Category</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Access</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Size</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Downloads</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Uploaded</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($documents as $doc)
                <tr class="border-b hover:bg-gray-50 transition">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">{{ $doc->file_icon }}</span>
                            <div>
                                <p class="font-medium text-gray-800">{{ $doc->title }}</p>
                                <p class="text-xs text-gray-400">{{ $doc->file_name }}@if($doc->version > 1) · v{{ $doc->version }}@endif</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-600 text-xs">{{ $doc->category->icon ?? '' }} {{ $doc->category->name ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $doc->access_level === 'all' ? 'bg-green-100 text-green-700' : ($doc->access_level === 'admin' ? 'bg-red-100 text-red-600' : 'bg-blue-100 text-blue-700') }}">
                            {{ ucfirst($doc->access_level) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $doc->file_size_formatted }}</td>
                    <td class="px-4 py-3 text-gray-500 text-xs text-center">{{ $doc->download_count }}</td>
                    <td class="px-4 py-3 text-gray-400 text-xs">{{ $doc->created_at->format('d M Y') }}<br>{{ $doc->uploadedBy->name }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'edit-doc-{{ $doc->id }}' }))"
                                class="text-xs text-gray-500 hover:underline">Edit</button>
                            <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'delete-doc-{{ $doc->id }}' }))"
                                class="text-xs text-red-400 hover:underline">Delete</button>
                        </div>
                    </td>
                </tr>

                {{-- Edit Modal --}}
                <x-modal name="edit-doc-{{ $doc->id }}" :show="old('_edit_id') == $doc->id && $errors->any()" maxWidth="lg">
                    <div class="bg-white rounded-xl overflow-hidden">
                        <div class="flex items-center justify-between px-6 py-4 border-b bg-gray-50">
                            <h2 class="text-base font-semibold text-gray-800">Edit Document</h2>
                            <button @click="show = false" class="text-gray-400 hover:text-gray-600 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        @php $isActive = old('_edit_id') == $doc->id && $errors->any(); @endphp
                        <form action="{{ route('admin.documents.update', $doc) }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                            @csrf @method('PATCH')
                            <input type="hidden" name="_edit_id" value="{{ $doc->id }}">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                                <input type="text" name="title" value="{{ $isActive ? old('title') : $doc->title }}"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                                    <select name="category_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ ($isActive ? old('category_id') : $doc->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Access Level</label>
                                    <select name="access_level" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        @foreach(['all' => 'All Users', 'resident' => 'Residents', 'admin' => 'Admin Only'] as $v => $l)
                                        <option value="{{ $v }}" {{ ($isActive ? old('access_level') : $doc->access_level) === $v ? 'selected' : '' }}>{{ $l }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Replace File <span class="text-gray-400 font-normal">(optional — creates new version)</span></label>
                                <input type="file" name="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            </div>
                            <div class="flex gap-3 pt-2 border-t">
                                <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">Save</button>
                                <button type="button" @click="show = false" class="text-gray-500 text-sm py-2 hover:text-gray-700">Cancel</button>
                            </div>
                        </form>
                    </div>
                </x-modal>

                {{-- Delete Modal --}}
                <x-modal name="delete-doc-{{ $doc->id }}" maxWidth="sm">
                    <div class="bg-white rounded-xl overflow-hidden">
                        <div class="px-6 py-5">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-800">Delete Document</h3>
                                    <p class="text-sm text-gray-500 mt-0.5">File will be permanently removed.</p>
                                </div>
                            </div>
                            <p class="text-sm text-gray-600 bg-gray-50 rounded-lg px-3 py-2 border font-medium">{{ $doc->title }}</p>
                        </div>
                        <div class="flex items-center gap-3 px-6 pb-5">
                            <form action="{{ route('admin.documents.destroy', $doc) }}" method="POST">
                                @csrf @method('DELETE')
                                <button type="submit" class="bg-red-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-red-700 transition">Delete</button>
                            </form>
                            <button @click="show = false" class="text-gray-500 text-sm hover:text-gray-700">Cancel</button>
                        </div>
                    </div>
                </x-modal>

                @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">No documents found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $documents->links() }}
</div>

{{-- Upload Document Modal --}}
<x-modal name="upload-document" :show="$errors->any() && !old('_edit_id')" maxWidth="lg">
    <div class="bg-white rounded-xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b bg-gray-50">
            <h2 class="text-base font-semibold text-gray-800">Upload Document</h2>
            <button @click="show = false" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form action="{{ route('admin.documents.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('title') border-red-400 @enderror"
                    placeholder="e.g. Community Bylaws 2025">
                @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description <span class="text-gray-400 font-normal">(optional)</span></label>
                <textarea name="description" rows="2"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="Brief description...">{{ old('description') }}</textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
                    <select name="category_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('category_id') border-red-400 @enderror">
                        <option value="">— Select —</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Access Level</label>
                    <select name="access_level" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="all" {{ old('access_level') === 'all' ? 'selected' : '' }}>All Users</option>
                        <option value="resident" {{ old('access_level') === 'resident' ? 'selected' : '' }}>Residents Only</option>
                        <option value="admin" {{ old('access_level') === 'admin' ? 'selected' : '' }}>Admin Only</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">File <span class="text-red-500">*</span></label>
                <input type="file" name="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm @error('file') border-red-400 @enderror">
                <p class="text-xs text-gray-400 mt-1">PDF, Word, Excel, images. Max 25MB. Stored privately.</p>
                @error('file')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="flex gap-3 pt-2 border-t">
                <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">Upload</button>
                <button type="button" @click="show = false" class="text-gray-500 text-sm py-2 hover:text-gray-700">Cancel</button>
            </div>
        </form>
    </div>
</x-modal>

{{-- Add Category Modal --}}
<x-modal name="add-category" maxWidth="sm">
    <div class="bg-white rounded-xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b bg-gray-50">
            <h2 class="text-base font-semibold text-gray-800">Add Category</h2>
            <button @click="show = false" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form action="{{ route('admin.document-categories.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Icon</label>
                    <input type="text" name="icon" value="{{ old('icon') }}" maxlength="4"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm text-center text-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="📁">
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="e.g. Bylaws">
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="flex gap-3 pt-2 border-t">
                <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">Create</button>
                <button type="button" @click="show = false" class="text-gray-500 text-sm py-2 hover:text-gray-700">Cancel</button>
            </div>
        </form>
    </div>
</x-modal>
@endsection
