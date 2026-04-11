<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DutyAssignment extends Model
{
    protected $fillable = [
        'roster_id',
        'user_id',
        'status',
        'notes',
        'is_voluntary',
    ];

    protected $casts = [
        'is_voluntary' => 'boolean',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function roster(): BelongsTo
    {
        return $this->belongsTo(DutyRoster::class, 'roster_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ─── Accessors ────────────────────────────────────────────────────────────

    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'assigned'  => 'bg-blue-100 text-blue-700',
            'confirmed' => 'bg-green-100 text-green-700',
            'declined'  => 'bg-red-100 text-red-600',
            'completed' => 'bg-gray-100 text-gray-600',
            default     => 'bg-gray-100 text-gray-500',
        };
    }
}
