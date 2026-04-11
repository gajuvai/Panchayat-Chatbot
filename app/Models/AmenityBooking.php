<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AmenityBooking extends Model
{
    protected $fillable = [
        'booking_code',
        'amenity_id',
        'user_id',
        'starts_at',
        'ends_at',
        'purpose',
        'guest_count',
        'status',
        'reviewed_by',
        'rejection_reason',
        'total_fee',
    ];

    protected $casts = [
        'starts_at'  => 'datetime',
        'ends_at'    => 'datetime',
        'total_fee'  => 'decimal:2',
        'guest_count'=> 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $booking) {
            do {
                $code = 'BK-' . strtoupper(Str::random(6));
            } while (self::where('booking_code', $code)->exists());

            $booking->booking_code = $code;
        });
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function amenity(): BelongsTo
    {
        return $this->belongsTo(Amenity::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // ─── Accessors ────────────────────────────────────────────────────────────

    public function getDurationMinutesAttribute(): int
    {
        return (int) $this->starts_at->diffInMinutes($this->ends_at);
    }

    public function getDurationLabelAttribute(): string
    {
        $m = $this->duration_minutes;
        $h = intdiv($m, 60);
        $rem = $m % 60;
        return ($h > 0 ? "{$h}h " : '') . ($rem > 0 ? "{$rem}m" : '');
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'pending'   => 'bg-yellow-100 text-yellow-700',
            'approved'  => 'bg-green-100 text-green-700',
            'rejected'  => 'bg-red-100 text-red-600',
            'cancelled' => 'bg-gray-100 text-gray-500',
            'completed' => 'bg-blue-100 text-blue-700',
            default     => 'bg-gray-100 text-gray-500',
        };
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, ['pending', 'approved'])
            && $this->starts_at->isFuture();
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeUpcoming($query)
    {
        return $query->where('starts_at', '>', now())
                     ->whereIn('status', ['pending', 'approved']);
    }

    public function scopePendingApproval($query)
    {
        return $query->where('status', 'pending');
    }
}
