<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DutyRoster extends Model
{
    protected $fillable = [
        'created_by',
        'title',
        'description',
        'type',
        'roster_date',
        'shift_start',
        'shift_end',
        'slots_required',
        'is_open_signup',
    ];

    protected $casts = [
        'roster_date'    => 'date',
        'is_open_signup' => 'boolean',
        'slots_required' => 'integer',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(DutyAssignment::class, 'roster_id');
    }

    public function assignedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'duty_assignments', 'roster_id', 'user_id')
                    ->withPivot(['status', 'notes', 'is_voluntary'])
                    ->withTimestamps();
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function isFull(): bool
    {
        return $this->assignments()
            ->whereIn('status', ['assigned', 'confirmed'])
            ->count() >= $this->slots_required;
    }

    public function isUserAssigned(int $userId): bool
    {
        return $this->assignments()->where('user_id', $userId)->exists();
    }

    public function filledSlots(): int
    {
        return $this->assignments()->whereIn('status', ['assigned', 'confirmed'])->count();
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'weekly_duty'      => 'Weekly Duty',
            'event_volunteer'  => 'Event Volunteer',
            'committee'        => 'Committee',
            default            => 'Other',
        };
    }

    public function getTypeIconAttribute(): string
    {
        return match($this->type) {
            'weekly_duty'     => '🗓',
            'event_volunteer' => '🙋',
            'committee'       => '🏛️',
            default           => '📋',
        };
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeUpcoming($query)
    {
        return $query->where('roster_date', '>=', today());
    }

    public function scopeOpenSignup($query)
    {
        return $query->where('is_open_signup', true);
    }
}
