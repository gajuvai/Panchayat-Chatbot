<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'title', 'description', 'venue', 'event_date', 'end_date',
        'max_attendees', 'is_rsvp_required', 'rsvp_deadline', 'status', 'banner_image',
    ];

    protected function casts(): array
    {
        return [
            'event_date'      => 'datetime',
            'end_date'        => 'datetime',
            'rsvp_deadline'   => 'datetime',
            'is_rsvp_required'=> 'boolean',
        ];
    }

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function rsvps(): HasMany
    {
        return $this->hasMany(EventRsvp::class);
    }

    public function getAttendeeCount(): int
    {
        return $this->rsvps()->where('status', 'attending')->sum('guests_count')
            + $this->rsvps()->where('status', 'attending')->count();
    }

    public function isUserAttending(User $user): bool
    {
        return $this->rsvps()->where('user_id', $user->id)->where('status', 'attending')->exists();
    }

    public function isFull(): bool
    {
        if (!$this->max_attendees) return false;
        return $this->getAttendeeCount() >= $this->max_attendees;
    }
}
