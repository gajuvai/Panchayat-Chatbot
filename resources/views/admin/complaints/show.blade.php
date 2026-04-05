@extends('layouts.app')
@section('title', 'Complaint: ' . $complaint->complaint_number)

@section('content')
<div class="max-w-4xl mx-auto space-y-5">
    <a href="{{ route('admin.complaints.index') }}" class="text-indigo-600 text-sm hover:underline">← Back to Complaints</a>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        {{-- Left: Details --}}
        <div class="lg:col-span-2 space-y-5">
            <div class="bg-white rounded-xl border p-6">
                <div class="flex items-center gap-2 flex-wrap mb-3">
                    <span class="font-mono text-sm text-gray-500">{{ $complaint->complaint_number }}</span>
                    <span class="px-2 py-0.5 rounded-full text-sm {{ $complaint->status->badgeClass() }}">{{ $complaint->status->label() }}</span>
                    <span class="px-2 py-0.5 rounded-full text-xs {{ $complaint->priority->badgeClass() }}">{{ $complaint->priority->label() }}</span>
                </div>
                <h2 class="text-xl font-semibold text-gray-800 mb-4">{{ $complaint->title }}</h2>
                <div class="grid grid-cols-2 gap-3 text-sm mb-4">
                    <div><span class="text-gray-500">Filed by:</span> <strong>{{ $complaint->is_anonymous ? 'Anonymous' : $complaint->user->name }}</strong></div>
                    <div><span class="text-gray-500">Category:</span> <strong>{{ $complaint->category?->name }}</strong></div>
                    @if($complaint->location)<div><span class="text-gray-500">Location:</span> <strong>{{ $complaint->location }}</strong></div>@endif
                    <div><span class="text-gray-500">Filed:</span> <strong>{{ $complaint->created_at->format('d M Y, h:i A') }}</strong></div>
                    @if($complaint->assignee)<div><span class="text-gray-500">Assigned to:</span> <strong>{{ $complaint->assignee->name }}</strong></div>@endif
                    @if($complaint->resolved_at)<div><span class="text-gray-500">Resolved:</span> <strong>{{ $complaint->resolved_at->format('d M Y') }}</strong></div>@endif
                </div>
                <div class="bg-gray-50 rounded-lg p-4 text-sm text-gray-700">{{ $complaint->description }}</div>
                @if($complaint->resolution_notes)
                <div class="mt-3 bg-green-50 border border-green-200 rounded-lg p-3 text-sm text-green-800">
                    <strong>Resolution Notes:</strong> {{ $complaint->resolution_notes }}
                </div>
                @endif
            </div>

            {{-- Media --}}
            @if($complaint->media->count())
            <div class="bg-white rounded-xl border p-5">
                <h3 class="font-semibold mb-3">Attachments</h3>
                <div class="grid grid-cols-2 gap-3">
                    @foreach($complaint->media as $media)
                    <div class="border rounded-lg p-3 text-sm">
                        @if($media->media_type === 'voice')
                            <p class="text-xs text-gray-500 mb-1">🎙 Voice</p>
                            <audio controls class="w-full h-8" src="{{ $media->file_url }}"></audio>
                            @if($media->transcription)<p class="text-xs text-gray-500 mt-1 italic">"{{ $media->transcription }}"</p>@endif
                        @elseif($media->media_type === 'image')
                            <img src="{{ $media->file_url }}" class="w-full h-20 object-cover rounded">
                        @else
                            <a href="{{ $media->file_url }}" target="_blank" class="text-indigo-600 hover:underline">📎 {{ $media->file_name }}</a>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Timeline --}}
            <div class="bg-white rounded-xl border p-5">
                <h3 class="font-semibold mb-4">Activity</h3>
                @forelse($complaint->updates as $update)
                <div class="flex gap-3 mb-4">
                    <div class="w-7 h-7 bg-indigo-100 rounded-full flex items-center justify-center text-xs font-bold text-indigo-700 flex-shrink-0">
                        {{ strtoupper(substr($update->user->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2 text-sm">
                            <span class="font-medium">{{ $update->user->name }}</span>
                            <span class="text-gray-400 text-xs">{{ $update->created_at->diffForHumans() }}</span>
                            @if($update->is_internal)<span class="text-xs bg-yellow-100 text-yellow-700 px-1.5 rounded">Internal</span>@endif
                        </div>
                        @if($update->old_status && $update->new_status)
                        <p class="text-xs text-gray-500">{{ ucfirst(str_replace('_',' ',$update->old_status)) }} → {{ ucfirst(str_replace('_',' ',$update->new_status)) }}</p>
                        @endif
                        <p class="text-sm text-gray-700 mt-0.5">{{ $update->message }}</p>
                    </div>
                </div>
                @empty
                <p class="text-sm text-gray-400">No activity yet.</p>
                @endforelse
            </div>
        </div>

        {{-- Right: Actions --}}
        <div class="space-y-4">
            {{-- Assign --}}
            <div class="bg-white rounded-xl border p-4">
                <h3 class="font-semibold text-sm mb-3">Assign Complaint</h3>
                <form method="POST" action="{{ route('admin.complaints.assign', $complaint) }}">
                    @csrf @method('PATCH')
                    <select name="assigned_to" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm mb-2">
                        <option value="">Unassigned</option>
                        @foreach($admins as $admin)
                        <option value="{{ $admin->id }}" {{ $complaint->assigned_to == $admin->id ? 'selected' : '' }}>{{ $admin->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded-lg text-sm hover:bg-indigo-700">Assign</button>
                </form>
            </div>

            {{-- Update status --}}
            <div class="bg-white rounded-xl border p-4">
                <h3 class="font-semibold text-sm mb-3">Update Status</h3>
                <form method="POST" action="{{ route('admin.complaints.status', $complaint) }}">
                    @csrf @method('PATCH')
                    <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm mb-2">
                        @foreach(['open','in_progress','resolved','closed','rejected'] as $s)
                        <option value="{{ $s }}" {{ $complaint->status->value === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                        @endforeach
                    </select>
                    <textarea name="message" placeholder="Status update message..." rows="2"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm mb-2" required></textarea>
                    <textarea name="resolution_notes" placeholder="Resolution notes (optional)..." rows="2"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm mb-2"></textarea>
                    <label class="flex items-center gap-2 text-xs text-gray-600 mb-2">
                        <input type="checkbox" name="is_internal" value="1" class="rounded"> Internal note only
                    </label>
                    <button type="submit" class="w-full bg-green-600 text-white py-2 rounded-lg text-sm hover:bg-green-700">Update Status</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
