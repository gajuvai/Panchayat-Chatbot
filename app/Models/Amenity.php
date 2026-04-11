<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class Amenity extends Model
{
    protected $fillable = [
        'name',
        'type',
        'description',
        'capacity',
        'requires_approval',
        'fee_per_hour',
        'opening_time',
        'closing_time',
        'available_days',
        'is_active',
        'photo_path',
    ];

    protected $casts = [
        'is_active'         => 'boolean',
        'requires_approval' => 'boolean',
        'available_days'    => 'array',
        'fee_per_hour'      => 'decimal:2',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function bookings(): HasMany
    {
        return $this->hasMany(AmenityBooking::class);
    }

    // ─── Accessors ────────────────────────────────────────────────────────────

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path ? Storage::disk('public')->url($this->photo_path) : null;
    }

    public function getTypeIconAttribute(): string
    {
        return match($this->type) {
            'parking' => '🅿️',
            'hall'    => '🏛️',
            'gym'     => '🏋️',
            'pool'    => '🏊',
            'garden'  => '🌿',
            default   => '🏢',
        };
    }

    public function getIsFreeAttribute(): bool
    {
        return (float) $this->fee_per_hour === 0.0;
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Check if the amenity has capacity for a new booking in the given window.
     */
    public function isAvailableAt(Carbon $start, Carbon $end): bool
    {
        $conflicting = $this->bookings()
            ->whereIn('status', ['approved', 'pending'])
            ->where('starts_at', '<', $end)
            ->where('ends_at', '>', $start)
            ->count();

        return $conflicting < $this->capacity;
    }

    /**
     * Calculate fee for a given number of minutes.
     */
    public function calculateFee(int $minutes): float
    {
        if ($this->fee_per_hour == 0) return 0.0;
        return round(($minutes / 60) * $this->fee_per_hour, 2);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
