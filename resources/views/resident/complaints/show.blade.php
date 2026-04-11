@extends('layouts.app')
@section('title', 'Complaint Details')

@section('content')
<div class="max-w-3xl mx-auto space-y-5">
    <div class="flex items-center justify-between">
        <a href="{{ route('resident.complaints.index') }}" class="text-indigo-600 text-sm hover:underline">← Back to My Complaints</a>
        @if($complaint->status->value === 'open')
        <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'edit-complaint' }))"
            class="border border-gray-300 text-gray-700 px-3 py-1.5 rounded-lg text-sm hover:bg-gray-50">Edit</button>
        @endif
    </div>

    {{-- Main card --}}
    <div class="bg-white rounded-xl border p-6">
        <div class="flex items-start justify-between gap-4 mb-4">
            <div>
                <div class="flex items-center gap-2 flex-wrap mb-2">
                    <span class="font-mono text-sm text-gray-500">{{ $complaint->complaint_number }}</span>
                    <span class="px-2 py-0.5 rounded-full text-sm {{ $complaint->status->badgeClass() }}">{{ $complaint->status->label() }}</span>
                    <span class="px-2 py-0.5 rounded-full text-xs {{ $complaint->priority->badgeClass() }}">{{ $complaint->priority->label() }} Priority</span>
                </div>
                <h2 class="text-xl font-semibold text-gray-800">{{ $complaint->title }}</h2>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-4 text-sm">
            <div><span class="text-gray-500">Category:</span> <span class="font-medium">{{ $complaint->category?->name }}</span></div>
            <div><span class="text-gray-500">Filed:</span> <span class="font-medium">{{ $complaint->created_at->format('d M Y') }}</span></div>
            @if($complaint->location)
            <div><span class="text-gray-500">Location:</span> <span class="font-medium">{{ $complaint->location }}</span></div>
            @endif
            @if($complaint->assignee)
            <div><span class="text-gray-500">Assigned to:</span> <span class="font-medium">{{ $complaint->assignee->name }}</span></div>
            @endif
            @if($complaint->resolved_at)
            <div><span class="text-gray-500">Resolved:</span> <span class="font-medium">{{ $complaint->resolved_at->format('d M Y') }}</span></div>
            @endif
        </div>

        <div class="bg-gray-50 rounded-lg p-4 text-sm text-gray-700 leading-relaxed">
            {{ $complaint->description }}
        </div>

        @if($complaint->resolution_notes)
        <div class="mt-4 bg-green-50 border border-green-200 rounded-lg p-4 text-sm text-green-800">
            <strong>Resolution Notes:</strong> {{ $complaint->resolution_notes }}
        </div>
        @endif

        {{-- Upvote --}}
        <div class="mt-4 flex items-center gap-3">
            <button onclick="toggleUpvote({{ $complaint->id }}, this)"
                class="flex items-center gap-1.5 text-sm border rounded-lg px-3 py-1.5 {{ $complaint->isUpvotedBy(auth()->user()) ? 'bg-indigo-50 border-indigo-300 text-indigo-700' : 'text-gray-600 hover:bg-gray-50' }} transition"
                data-voted="{{ $complaint->isUpvotedBy(auth()->user()) ? '1' : '0' }}">
                👍 <span id="upvote-count">{{ $complaint->upvotes }}</span> Upvote{{ $complaint->upvotes !== 1 ? 's' : '' }}
            </button>
        </div>
    </div>

    {{-- Media attachments --}}
    @if($complaint->media->count())
    <div class="bg-white rounded-xl border p-5">
        <h3 class="font-semibold text-gray-800 mb-3">Attachments</h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            @foreach($complaint->media as $media)
            <div class="border rounded-lg p-3 text-sm">
                @if($media->media_type === 'voice')
                    <p class="text-xs text-gray-500 mb-1">🎙 Voice Recording</p>
                    <audio controls class="w-full h-8" src="{{ $media->file_url }}"></audio>
                    @if($media->transcription)
                    <p class="text-xs text-gray-500 mt-2 italic">"{{ $media->transcription }}"</p>
                    @endif
                @elseif($media->media_type === 'image')
                    <img src="{{ $media->file_url }}" alt="{{ $media->file_name }}" class="w-full h-24 object-cover rounded">
                @else
                    <a href="{{ $media->file_url }}" target="_blank" class="text-indigo-600 hover:underline">📎 {{ $media->file_name }}</a>
                @endif
                <p class="text-xs text-gray-400 mt-1">{{ $media->file_size_formatted }}</p>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Timeline --}}
    <div class="bg-white rounded-xl border p-5">
        <h3 class="font-semibold text-gray-800 mb-4">Activity Timeline</h3>
        @forelse($complaint->updates->where('is_internal', false) as $update)
        <div class="flex gap-3 mb-4">
            <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center flex-shrink-0 text-indigo-700 text-xs font-bold">
                {{ strtoupper(substr($update->user->name, 0, 1)) }}
            </div>
            <div class="flex-1">
                <div class="flex items-center gap-2 text-sm">
                    <span class="font-medium text-gray-800">{{ $update->user->name }}</span>
                    <span class="text-gray-400 text-xs">{{ $update->created_at->diffForHumans() }}</span>
                </div>
                @if($update->old_status && $update->new_status)
                <p class="text-xs text-gray-500 mt-0.5">Status changed: {{ ucfirst(str_replace('_',' ',$update->old_status)) }} → {{ ucfirst(str_replace('_',' ',$update->new_status)) }}</p>
                @endif
                <p class="text-sm text-gray-700 mt-1">{{ $update->message }}</p>
            </div>
        </div>
        @empty
        <p class="text-sm text-gray-400">No activity yet.</p>
        @endforelse
    </div>

    {{-- Feedback form (for resolved complaints) --}}
    @if($complaint->status->value === 'resolved' && !$complaint->feedback)
    <div class="bg-white rounded-xl border p-5">
        <h3 class="font-semibold text-gray-800 mb-3">Rate the Resolution</h3>
        <form method="POST" action="{{ route('resident.feedback.store') }}">
            @csrf
            <input type="hidden" name="complaint_id" value="{{ $complaint->id }}">
            <input type="hidden" name="category" value="resolution">
            <div class="flex gap-2 mb-3">
                @for($i = 1; $i <= 5; $i++)
                <label class="cursor-pointer">
                    <input type="radio" name="rating" value="{{ $i }}" class="sr-only peer" required>
                    <span class="text-2xl peer-checked:scale-110 transition select-none hover:scale-110">⭐</span>
                </label>
                @endfor
            </div>
            <textarea name="comment" placeholder="Any additional comments..." rows="2"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm mb-3"></textarea>
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700">Submit Feedback</button>
        </form>
    </div>
    @endif
</div>

{{-- Edit Complaint Modal (only shown for open complaints) --}}
@if($complaint->status->value === 'open')
<x-modal name="edit-complaint" :show="$errors->isNotEmpty()" maxWidth="xl">
    <div class="bg-white rounded-xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b bg-gray-50">
            <div>
                <h2 class="text-base font-semibold text-gray-800">Edit Complaint</h2>
                <p class="text-xs text-gray-500 mt-0.5">{{ $complaint->complaint_number }}</p>
            </div>
            <button @click="show = false" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="overflow-y-auto max-h-[80vh]">
        <form method="POST" action="{{ route('resident.complaints.update', $complaint) }}" class="p-6 space-y-4">
            @csrf @method('PATCH')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title', $complaint->title) }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('title') border-red-400 @enderror">
                @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description <span class="text-red-500">*</span></label>
                <textarea name="description" rows="5"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('description') border-red-400 @enderror">{{ old('description', $complaint->description) }}</textarea>
                @error('description')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                    <select name="priority" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach(['low','medium','high','urgent'] as $p)
                        <option value="{{ $p }}" {{ old('priority', $complaint->priority->value) === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                    <input type="text" name="location" value="{{ old('location', $complaint->location) }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="e.g. Block A, Floor 2">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                <div class="w-full border border-gray-200 bg-gray-50 rounded-lg px-3 py-2 text-sm text-gray-600">
                    {{ $complaint->category?->name ?? '—' }}
                </div>
                <p class="text-xs text-gray-400 mt-1">Category cannot be changed after filing.</p>
            </div>
            <div class="flex gap-3 pt-2 border-t">
                <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">Update Complaint</button>
                <button type="button" @click="show = false" class="border border-gray-300 text-gray-700 px-5 py-2 rounded-lg text-sm hover:bg-gray-50 transition">Cancel</button>
            </div>
        </form>
        </div>
    </div>
</x-modal>
@endif

<script>
function toggleUpvote(id, btn) {
    fetch(`/resident/complaints/${id}/upvote`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('upvote-count').textContent = data.count;
        if (data.voted) {
            btn.classList.add('bg-indigo-50', 'border-indigo-300', 'text-indigo-700');
        } else {
            btn.classList.remove('bg-indigo-50', 'border-indigo-300', 'text-indigo-700');
        }
    });
}
</script>
@endsection
