<div class="flex items-center justify-between gap-3 bg-white rounded-lg border px-4 py-3 text-sm">
    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2 flex-wrap">
            <span class="font-mono text-xs font-bold text-indigo-600">{{ $pass->pass_code }}</span>
            <span class="text-xs px-1.5 py-0.5 rounded {{ $pass->status->badgeClass() }}">{{ $pass->status->label() }}</span>
        </div>
        <p class="font-medium text-gray-800 mt-0.5">{{ $pass->visitor_name }}</p>
        <div class="flex flex-wrap items-center gap-x-3 gap-y-0.5 text-xs text-gray-500 mt-0.5">
            <span>Flat {{ $pass->resident->block ?? '' }}-{{ $pass->resident->flat_number ?? '—' }} ({{ $pass->resident->name }})</span>
            @if($pass->expected_from) <span>🕐 {{ $pass->expected_from }}–{{ $pass->expected_to ?? '?' }}</span>@endif
            @if($pass->vehicle_number) <span>🚗 {{ $pass->vehicle_number }}</span>@endif
            @if($pass->visitor_phone) <span>📞 {{ $pass->visitor_phone }}</span>@endif
        </div>
        @if($pass->checked_in_at && $action !== 'check-in')
        <p class="text-xs text-gray-400 mt-0.5">
            In: {{ $pass->checked_in_at->format('h:i A') }}
            @if($pass->checked_out_at) · Out: {{ $pass->checked_out_at->format('h:i A') }}@endif
        </p>
        @endif
        @if($pass->notes)
        <p class="text-xs text-amber-600 mt-0.5 italic">📝 {{ $pass->notes }}</p>
        @endif
    </div>

    <div class="flex-shrink-0 flex flex-col gap-1.5">
        @if($action === 'check-in')
            @if($pass->status->value === 'pending')
            <form action="{{ route('security.visitors.approve', $pass) }}" method="POST">
                @csrf @method('PATCH')
                <button type="submit"
                    class="text-xs bg-blue-600 text-white px-3 py-1.5 rounded-lg hover:bg-blue-700 transition w-full">
                    Approve
                </button>
            </form>
            @endif
            <form action="{{ route('security.visitors.check-in', $pass) }}" method="POST">
                @csrf @method('PATCH')
                <button type="submit"
                    class="text-xs bg-green-600 text-white px-3 py-1.5 rounded-lg hover:bg-green-700 transition w-full">
                    Check In
                </button>
            </form>
        @elseif($action === 'check-out')
            <form action="{{ route('security.visitors.check-out', $pass) }}" method="POST">
                @csrf @method('PATCH')
                <button type="submit"
                    class="text-xs bg-gray-600 text-white px-3 py-1.5 rounded-lg hover:bg-gray-700 transition w-full">
                    Check Out
                </button>
            </form>
        @endif
    </div>
</div>
