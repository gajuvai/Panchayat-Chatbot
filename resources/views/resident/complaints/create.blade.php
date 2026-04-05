@extends('layouts.app')
@section('title', 'File a Complaint')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-6">File a New Complaint</h2>

        <form method="POST" action="{{ route('resident.complaints.store') }}" enctype="multipart/form-data" id="complaintForm">
            @csrf

            <div class="space-y-5">
                {{-- Category --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
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
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                    <input type="text" id="title" name="title" value="{{ old('title') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Brief title of your complaint">
                    @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Description --}}
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description <span class="text-red-500">*</span></label>
                    <textarea id="description" name="description" rows="4"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Describe the issue in detail...">{{ old('description') }}</textarea>
                    @error('description')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Priority & Location --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="priority" class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                        <select id="priority" name="priority" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            @foreach(['low','medium','high','urgent'] as $p)
                            <option value="{{ $p }}" {{ old('priority','medium') === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="location" class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                        <input type="text" id="location" name="location" value="{{ old('location') }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="e.g. Block A, Floor 2">
                    </div>
                </div>

                {{-- Voice Recorder --}}
                <div x-data="voiceRecorder()" class="border border-dashed border-gray-300 rounded-lg p-4">
                    <p class="text-sm font-medium text-gray-700 mb-3">🎙 Voice Recording (optional)</p>
                    <div class="flex items-center gap-3">
                        <button type="button" @click="toggleRecording()"
                            :class="recording ? 'bg-red-500 hover:bg-red-600' : 'bg-indigo-600 hover:bg-indigo-700'"
                            class="text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center gap-2">
                            <span x-text="recording ? '⏹ Stop' : '⏺ Record'"></span>
                        </button>
                        <span x-show="recording" class="text-red-500 text-sm animate-pulse" x-text="'Recording... ' + formatTime(elapsed)"></span>
                        <span x-show="audioBlob && !recording" class="text-green-600 text-sm">✓ Recording ready</span>
                    </div>
                    <audio x-show="audioUrl" :src="audioUrl" controls class="mt-3 w-full h-8"></audio>
                    <input type="file" name="voice_attachment" x-ref="voiceInput" class="hidden">
                </div>

                {{-- File attachments --}}
                <div>
                    <label for="attachments" class="block text-sm font-medium text-gray-700 mb-1">Attachments (optional)</label>
                    <input type="file" id="attachments" name="attachments[]" multiple accept="image/*,.pdf,.doc,.docx"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <p class="text-xs text-gray-500 mt-1">Images, PDF, Word documents. Max 10MB each.</p>
                </div>

                {{-- Anonymous --}}
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="is_anonymous" name="is_anonymous" value="1" {{ old('is_anonymous') ? 'checked' : '' }}
                        class="rounded border-gray-300 text-indigo-600">
                    <label for="is_anonymous" class="text-sm text-gray-700">Submit anonymously</label>
                </div>
            </div>

            <div class="flex gap-3 mt-6">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                    Submit Complaint
                </button>
                <a href="{{ route('resident.complaints.index') }}" class="border border-gray-300 text-gray-700 px-6 py-2 rounded-lg text-sm hover:bg-gray-50 transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

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
