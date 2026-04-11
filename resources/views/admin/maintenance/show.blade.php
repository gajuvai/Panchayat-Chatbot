@extends('layouts.app')
@section('title', $maintenance->request_number)

@section('content')
<div class="max-w-3xl mx-auto space-y-4">

    <div class="flex items-center justify-between flex-wrap gap-3">
        <a href="{{ route('admin.maintenance.index') }}" class="text-indigo-600 text-sm hover:underline">← Back to Requests</a>
        @if(!$maintenance->status->isTerminal())
        <div class="flex gap-2">
            <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'update-status' }))"
                class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                Update Status
            </button>
            <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'upload-media' }))"
                class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm hover:bg-gray-50 transition">
                Upload Photos
            </button>
        </div>
        @endif
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif

    {{-- Main Card --}}
    <div class="bg-white rounded-xl border p-6">
        <div class="flex items-start justify-between gap-4 mb-4">
            <div>
                <div class="flex items-center gap-2 flex-wrap mb-1">
                    <span class="font-mono text-sm font-bold text-indigo-600">{{ $maintenance->request_number }}</span>
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $maintenance->status->badgeClass() }}">{{ $maintenance->status->label() }}</span>
                    @php
                        $pc = match($maintenance->priority) {
                            'urgent' => 'bg-red-100 text-red-700',
                            'high'   => 'bg-orange-100 text-orange-700',
                            'medium' => 'bg-yellow-100 text-yellow-700',
                            default  => 'bg-gray-100 text-gray-500',
                        };
                    @endphp
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $pc }}">{{ ucfirst($maintenance->priority) }} Priority</span>
                </div>
                <h1 class="text-xl font-bold text-gray-800">{{ $maintenance->title }}</h1>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm mb-4">
            <div><span class="text-gray-500">Requested by:</span><br><span class="font-medium">{{ $maintenance->requestedBy->name }}</span>
                @if($maintenance->requestedBy->flat_number)
                <br><span class="text-xs text-gray-400">Flat {{ $maintenance->requestedBy->block }}-{{ $maintenance->requestedBy->flat_number }}</span>
                @endif
            </div>
            @if($maintenance->category)
            <div><span class="text-gray-500">Category:</span><br><span class="font-medium">{{ $maintenance->category->name }}</span></div>
            @endif
            @if($maintenance->location)
            <div><span class="text-gray-500">Location:</span><br><span class="font-medium">{{ $maintenance->location }}</span></div>
            @endif
            <div><span class="text-gray-500">Submitted:</span><br><span class="font-medium">{{ $maintenance->created_at->format('d M Y, h:i A') }}</span></div>
            @if($maintenance->assignedTo)
            <div><span class="text-gray-500">Assigned to:</span><br><span class="font-medium">{{ $maintenance->assignedTo->name }}</span></div>
            @endif
            @if($maintenance->scheduled_at)
            <div><span class="text-gray-500">Scheduled:</span><br><span class="font-medium">{{ $maintenance->scheduled_at->format('d M Y, h:i A') }}</span></div>
            @endif
            @if($maintenance->vendor_name)
            <div><span class="text-gray-500">Vendor:</span><br><span class="font-medium">{{ $maintenance->vendor_name }}</span>
                @if($maintenance->vendor_contact) <br><span class="text-xs text-gray-400">{{ $maintenance->vendor_contact }}</span>@endif
            </div>
            @endif
            @if($maintenance->estimated_cost !== null)
            <div><span class="text-gray-500">Est. Cost:</span><br><span class="font-medium">Rs. {{ number_format($maintenance->estimated_cost, 2) }}</span></div>
            @endif
            @if($maintenance->actual_cost !== null)
            <div><span class="text-gray-500">Actual Cost:</span><br><span class="font-medium text-green-700">Rs. {{ number_format($maintenance->actual_cost, 2) }}</span></div>
            @endif
            @if($maintenance->completed_at)
            <div><span class="text-gray-500">Completed:</span><br><span class="font-medium text-green-700">{{ $maintenance->completed_at->format('d M Y') }}</span></div>
            @endif
        </div>

        <div class="border-t pt-4">
            <p class="text-sm font-medium text-gray-700 mb-2">Description</p>
            <p class="text-sm text-gray-600 bg-gray-50 rounded-lg p-4 leading-relaxed">{{ $maintenance->description }}</p>
        </div>

        @if($maintenance->rejection_reason)
        <div class="mt-4 bg-red-50 border border-red-200 rounded-lg p-4">
            <p class="text-sm font-medium text-red-700">Rejection Reason</p>
            <p class="text-sm text-red-600 mt-1">{{ $maintenance->rejection_reason }}</p>
        </div>
        @endif

        @if($maintenance->completion_notes)
        <div class="mt-4 bg-green-50 border border-green-200 rounded-lg p-4">
            <p class="text-sm font-medium text-green-700">Completion Notes</p>
            <p class="text-sm text-green-600 mt-1">{{ $maintenance->completion_notes }}</p>
        </div>
        @endif

        {{-- Assign section --}}
        @if(!$maintenance->status->isTerminal())
        <div class="mt-4 pt-4 border-t">
            <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'assign-modal' }))"
                class="text-sm text-indigo-600 hover:underline">
                {{ $maintenance->assignedTo ? 'Reassign / Update Details' : '+ Assign staff & set estimate' }}
            </button>
        </div>
        @endif
    </div>

    {{-- Media --}}
    @if($maintenance->media->isNotEmpty())
    <div class="bg-white rounded-xl border p-5">
        <h2 class="text-sm font-semibold text-gray-700 mb-3">Photos & Documents</h2>
        @foreach(['before','during','after','document'] as $stage)
        @php $stageMedia = $maintenance->media->where('stage', $stage); @endphp
        @if($stageMedia->isNotEmpty())
        <div class="mb-4">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">{{ ucfirst($stage) }}</p>
            <div class="grid grid-cols-3 sm:grid-cols-4 gap-2">
                @foreach($stageMedia as $media)
                @if($media->is_image)
                <a href="{{ $media->file_url }}" target="_blank" class="block">
                    <img src="{{ $media->file_url }}" alt="{{ $media->file_name }}"
                        class="w-full h-20 object-cover rounded-lg border hover:opacity-90 transition">
                </a>
                @else
                <a href="{{ $media->file_url }}" target="_blank"
                    class="flex items-center gap-1.5 text-xs text-indigo-600 bg-indigo-50 border border-indigo-100 rounded-lg px-3 py-2 hover:bg-indigo-100 transition">
                    📎 {{ Str::limit($media->file_name, 18) }}
                </a>
                @endif
                @endforeach
            </div>
        </div>
        @endif
        @endforeach
    </div>
    @endif

</div>

{{-- Update Status Modal --}}
@php $nextStatuses = $maintenance->status->nextAllowedStatuses(); @endphp
@if(!empty($nextStatuses))
<x-modal name="update-status" :show="$errors->hasAny(['status','scheduled_at','rejection_reason','completion_notes'])" maxWidth="lg">
    <div class="bg-white rounded-xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b bg-gray-50">
            <h2 class="text-base font-semibold text-gray-800">Update Status</h2>
            <button @click="show = false" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form action="{{ route('admin.maintenance.status', $maintenance) }}" method="POST"
              class="p-6 space-y-4" x-data="{ status: '{{ old('status', $nextStatuses[0]->value) }}' }">
            @csrf @method('PATCH')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">New Status <span class="text-red-500">*</span></label>
                <div class="flex flex-wrap gap-2">
                    @foreach($nextStatuses as $s)
                    <label class="cursor-pointer">
                        <input type="radio" name="status" value="{{ $s->value }}" x-model="status" class="sr-only">
                        <span :class="status === '{{ $s->value }}' ? 'ring-2 ring-indigo-500' : ''"
                            class="inline-block px-4 py-2 rounded-lg border text-sm font-medium {{ $s->badgeClass() }} transition">
                            {{ $s->label() }}
                        </span>
                    </label>
                    @endforeach
                </div>
                @error('status')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Scheduled at --}}
            <div x-show="status === 'scheduled'">
                <label class="block text-sm font-medium text-gray-700 mb-1">Schedule Date & Time</label>
                <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @error('scheduled_at')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Rejection reason --}}
            <div x-show="status === 'rejected'">
                <label class="block text-sm font-medium text-gray-700 mb-1">Rejection Reason</label>
                <textarea name="rejection_reason" rows="3"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="Explain why this request is being rejected...">{{ old('rejection_reason') }}</textarea>
                @error('rejection_reason')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Completion notes + actual cost --}}
            <div x-show="status === 'completed'" class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Completion Notes</label>
                    <textarea name="completion_notes" rows="3"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Summary of work done...">{{ old('completion_notes', $maintenance->completion_notes) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Actual Cost (Rs.)</label>
                    <input type="number" name="actual_cost" value="{{ old('actual_cost', $maintenance->actual_cost) }}"
                        step="0.01" min="0"
                        class="w-40 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="0.00">
                </div>
            </div>

            <div class="flex gap-3 pt-2 border-t">
                <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">Update</button>
                <button type="button" @click="show = false" class="text-gray-500 text-sm py-2 hover:text-gray-700">Cancel</button>
            </div>
        </form>
    </div>
</x-modal>
@endif

{{-- Assign Modal --}}
<x-modal name="assign-modal" :show="$errors->hasAny(['assigned_to','estimated_cost'])" maxWidth="md">
    <div class="bg-white rounded-xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b bg-gray-50">
            <h2 class="text-base font-semibold text-gray-800">Assign & Set Details</h2>
            <button @click="show = false" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form action="{{ route('admin.maintenance.assign', $maintenance) }}" method="POST" class="p-6 space-y-4">
            @csrf @method('PATCH')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Assign To <span class="text-gray-400 font-normal">(optional)</span></label>
                <select name="assigned_to" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">— Unassigned —</option>
                    @foreach($staff as $s)
                    <option value="{{ $s->id }}" {{ old('assigned_to', $maintenance->assigned_to) == $s->id ? 'selected' : '' }}>
                        {{ $s->name }} ({{ $s->role->display_name }})
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Vendor Name</label>
                    <input type="text" name="vendor_name" value="{{ old('vendor_name', $maintenance->vendor_name) }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Optional">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Vendor Contact</label>
                    <input type="text" name="vendor_contact" value="{{ old('vendor_contact', $maintenance->vendor_contact) }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Phone">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Estimated Cost (Rs.) <span class="text-gray-400 font-normal">(optional)</span></label>
                <input type="number" name="estimated_cost" value="{{ old('estimated_cost', $maintenance->estimated_cost) }}"
                    step="0.01" min="0"
                    class="w-40 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="0.00">
            </div>
            <div class="flex gap-3 pt-2 border-t">
                <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">Save</button>
                <button type="button" @click="show = false" class="text-gray-500 text-sm py-2 hover:text-gray-700">Cancel</button>
            </div>
        </form>
    </div>
</x-modal>

{{-- Upload Media Modal --}}
<x-modal name="upload-media" maxWidth="md">
    <div class="bg-white rounded-xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b bg-gray-50">
            <h2 class="text-base font-semibold text-gray-800">Upload Photos / Documents</h2>
            <button @click="show = false" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form action="{{ route('admin.maintenance.media.store', $maintenance) }}" method="POST"
              enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Stage <span class="text-red-500">*</span></label>
                <select name="stage" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="before">Before Work</option>
                    <option value="during">During Work</option>
                    <option value="after">After / Completion</option>
                    <option value="document">Document / Invoice</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Files <span class="text-red-500">*</span></label>
                <input type="file" name="files[]" multiple accept="image/*,.pdf,.doc,.docx"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <p class="text-xs text-gray-400 mt-1">Images, PDF, Word. Max 5 files, 10MB each.</p>
            </div>
            <div class="flex gap-3 pt-2 border-t">
                <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">Upload</button>
                <button type="button" @click="show = false" class="text-gray-500 text-sm py-2 hover:text-gray-700">Cancel</button>
            </div>
        </form>
    </div>
</x-modal>
@endsection
