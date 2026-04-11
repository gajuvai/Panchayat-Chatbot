@extends('layouts.app')
@section('title', 'My Complaints')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <p class="text-gray-500 text-sm">{{ $complaints->total() }} complaint(s) found</p>
        <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'create-complaint' }))"
            class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Complaint
        </button>
    </div>

    {{-- Filters --}}
    <form method="GET" class="bg-white rounded-xl border p-4 flex flex-wrap gap-3">
        <select name="status" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
            <option value="">All Statuses</option>
            @foreach(['open','in_progress','resolved','closed','rejected'] as $s)
            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
            @endforeach
        </select>
        <select name="category" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
            <option value="">All Categories</option>
            @foreach($categories as $cat)
            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="bg-gray-100 text-gray-700 px-4 py-1.5 rounded-lg text-sm hover:bg-gray-200 transition">Filter</button>
        <a href="{{ route('resident.complaints.index') }}" class="text-gray-500 text-sm py-1.5 hover:text-gray-700">Clear</a>
    </form>

    {{-- Complaints list --}}
    @forelse($complaints as $complaint)
    <div class="bg-white rounded-xl border p-5 hover:shadow-sm transition">
        <div class="flex items-start justify-between gap-4">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="font-mono text-xs text-gray-500">{{ $complaint->complaint_number }}</span>
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $complaint->status->badgeClass() }}">{{ $complaint->status->label() }}</span>
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $complaint->priority->badgeClass() }}">{{ $complaint->priority->label() }}</span>
                </div>
                <h3 class="font-semibold text-gray-800 mt-1">{{ $complaint->title }}</h3>
                <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ $complaint->description }}</p>
                <div class="flex items-center gap-4 mt-2 text-xs text-gray-400">
                    <span>{{ $complaint->category?->name }}</span>
                    <span>{{ $complaint->created_at->diffForHumans() }}</span>
                    @if($complaint->location)
                    <span>📍 {{ $complaint->location }}</span>
                    @endif
                    @if($complaint->upvotes > 0)
                    <span>👍 {{ $complaint->upvotes }}</span>
                    @endif
                </div>
            </div>
            <a href="{{ route('resident.complaints.show', $complaint) }}" class="flex-shrink-0 text-indigo-600 text-sm hover:underline">View →</a>
        </div>
    </div>
    @empty
    <div class="bg-white rounded-xl border p-12 text-center">
        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        <p class="text-gray-500 text-sm">No complaints yet.</p>
        <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'create-complaint' }))"
            class="mt-2 inline-block text-indigo-600 text-sm hover:underline">File your first complaint</button>
    </div>
    @endforelse

    {{ $complaints->links() }}
</div>

{{-- Create Complaint Modal --}}
<x-modal name="create-complaint" :show="$errors->any()" maxWidth="2xl">
    <div class="bg-white rounded-xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b bg-gray-50">
            <h2 class="text-base font-semibold text-gray-800">File a New Complaint</h2>
            <button @click="show = false" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="overflow-y-auto max-h-[80vh]">
        <form method="POST" action="{{ route('resident.complaints.store') }}" enctype="multipart/form-data" class="p-6 space-y-5">
            @csrf

            {{-- Category --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Category <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                    @foreach($categories as $cat)
                    <label class="cursor-pointer">
                        <input type="radio" name="category_id" value="{{ $cat->id }}" class="sr-only peer" {{ old('category_id') == $cat->id ? 'checked' : '' }}>
                        <div class="border-2 rounded-lg p-3 text-center text-sm peer-checked:border-indigo-500 peer-checked:bg-indigo-50 hover:border-indigo-300 transition">
                            {{ $cat->name }}
                        </div>
                    </label>
                    @endforeach
                </div>
                @error('category_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Title --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="Brief title of your complaint">
                @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Description --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description <span class="text-red-500">*</span></label>
                <textarea name="description" rows="4"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="Describe the issue in detail...">{{ old('description') }}</textarea>
                @error('description')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Priority & Location --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                    <select name="priority" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach(['low','medium','high','urgent'] as $p)
                        <option value="{{ $p }}" {{ old('priority','medium') === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                    <input type="text" name="location" value="{{ old('location') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="e.g. Block A, Floor 2">
                </div>
            </div>

            {{-- Voice Recorder --}}
            <div x-data="voiceRecorder()" class="border border-dashed border-gray-300 rounded-lg p-4">
                <p class="text-sm font-medium text-gray-700 mb-3">Voice Recording <span class="text-gray-400 font-normal">(optional)</span></p>
                <div class="flex items-center gap-3">
                    <button type="button" @click="toggleRecording()"
                        :class="recording ? 'bg-red-500 hover:bg-red-600' : 'bg-indigo-600 hover:bg-indigo-700'"
                        class="text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center gap-2">
                        <span x-text="recording ? '⏹ Stop' : '⏺ Record'"></span>
                    </button>
                    <span x-show="recording" class="text-red-500 text-sm animate-pulse" x-text="'Recording... ' + formatTime(elapsed)"></span>
                    <span x-show="audioBlob && !recording" class="text-green-600 text-sm">Recording ready</span>
                </div>
                <audio x-show="audioUrl" :src="audioUrl" controls class="mt-3 w-full h-8"></audio>
                <input type="file" name="voice_attachment" x-ref="voiceInput" class="hidden">
            </div>

            {{-- File Attachments --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Attachments <span class="text-gray-400 font-normal">(optional)</span></label>
                <input type="file" name="attachments[]" multiple accept="image/*,.pdf,.doc,.docx"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <p class="text-xs text-gray-500 mt-1">Images, PDF, Word documents. Max 10MB each.</p>
            </div>

            {{-- Anonymous --}}
            <div class="flex items-center gap-2">
                <input type="checkbox" id="is_anonymous_modal" name="is_anonymous" value="1" {{ old('is_anonymous') ? 'checked' : '' }}
                    class="rounded border-gray-300 text-indigo-600">
                <label for="is_anonymous_modal" class="text-sm text-gray-700">Submit anonymously</label>
            </div>

            <div class="flex gap-3 pt-2 border-t">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                    Submit Complaint
                </button>
                <button type="button" @click="show = false" class="border border-gray-300 text-gray-700 px-6 py-2 rounded-lg text-sm hover:bg-gray-50 transition">
                    Cancel
                </button>
            </div>
        </form>
        </div>
    </div>
</x-modal>

<script>
function voiceRecorder() {
    return {
        recording: false,
        mediaRecorder: null,
        audioBlob: null,
        audioUrl: null,
        chunks: [],
        elapsed: 0,
        timer: null,
        async toggleRecording() {
            if (!this.recording) {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                this.chunks = [];
                this.mediaRecorder = new MediaRecorder(stream);
                this.mediaRecorder.ondataavailable = e => this.chunks.push(e.data);
                this.mediaRecorder.onstop = () => {
                    this.audioBlob = new Blob(this.chunks, { type: 'audio/webm' });
                    this.audioUrl = URL.createObjectURL(this.audioBlob);
                    const file = new File([this.audioBlob], 'voice_complaint.webm', { type: 'audio/webm' });
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    this.$refs.voiceInput.files = dt.files;
                };
                this.mediaRecorder.start();
                this.recording = true;
                this.elapsed = 0;
                this.timer = setInterval(() => this.elapsed++, 1000);
            } else {
                this.mediaRecorder.stop();
                this.mediaRecorder.stream.getTracks().forEach(t => t.stop());
                clearInterval(this.timer);
                this.recording = false;
            }
        },
        formatTime(s) {
            return `${Math.floor(s/60)}:${String(s%60).padStart(2,'0')}`;
        }
    }
}
</script>
@endsection
