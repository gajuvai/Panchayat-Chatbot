<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class LostAndFoundItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'description',
        'location',
        'date_occurred',
        'contact_info',
        'photo_path',
        'is_resolved',
        'resolved_at',
    ];

    protected $casts = [
        'date_occurred' => 'date',
        'resolved_at'   => 'datetime',
        'is_resolved'   => 'boolean',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ─── Accessors ────────────────────────────────────────────────────────────

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path ? Storage::disk('public')->url($this->photo_path) : null;
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'lost'  => 'Lost',
            'found' => 'Found',
            default => ucfirst($this->type),
        };
    }

    public function getTypeBadgeClassAttribute(): string
    {
        return match($this->type) {
            'lost'  => 'bg-red-100 text-red-700',
            'found' => 'bg-green-100 text-green-700',
            default => 'bg-gray-100 text-gray-600',
        };
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_resolved', false);
    }

    public function scopeLost($query)
    {
        return $query->where('type', 'lost');
    }

    public function scopeFound($query)
    {
        return $query->where('type', 'found');
    }
}
