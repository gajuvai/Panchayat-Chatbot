@extends('layouts.app')
@section('title', 'Notifications')

@section('content')
<div class="max-w-2xl mx-auto space-y-3">
    @if(auth()->user()->unreadNotifications->count())
    <div class="flex justify-end">
        <form method="POST" action="{{ route('notifications.read-all') }}">
            @csrf @method('PATCH')
            <button type="submit" class="text-indigo-600 text-sm hover:underline">Mark all as read</button>
        </form>
    </div>
    @endif

    @forelse($notifications as $notification)
    <div class="bg-white rounded-xl border p-4 {{ is_null($notification->read_at) ? 'border-l-4 border-l-indigo-500' : '' }} hover:shadow-sm transition">
        <div class="flex items-start justify-between gap-3">
            <div class="flex-1">
                <p class="text-sm font-medium text-gray-800">{{ $notification->data['title'] ?? 'Notification' }}</p>
                <p class="text-sm text-gray-600 mt-0.5">{{ $notification->data['message'] ?? json_encode($notification->data) }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
            </div>
            @if(is_null($notification->read_at))
            <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                @csrf @method('PATCH')
                <button type="submit" class="text-xs text-indigo-600 hover:underline flex-shrink-0">Mark read</button>
            </form>
            @else
            <span class="text-xs text-gray-400">Read</span>
            @endif
        </div>
    </div>
    @empty
    <div class="bg-white rounded-xl border p-12 text-center text-gray-400">
        <p>No notifications yet.</p>
    </div>
    @endforelse

    {{ $notifications->links() }}
</div>
@endsection
