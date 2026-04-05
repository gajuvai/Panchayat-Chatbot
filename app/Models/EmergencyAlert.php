<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmergencyAlert extends Model
{
    protected $fillable = [
        'triggered_by', 'alert_type', 'message', 'is_active', 'resolved_at', 'resolved_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active'   => 'boolean',
            'resolved_at' => 'datetime',
        ];
    }

    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function alertTypeBadgeClass(): string
    {
        return match($this->alert_type) {
            'fire'             => 'bg-red-600 text-white',
            'medical'          => 'bg-blue-600 text-white',
            'security'         => 'bg-orange-600 text-white',
            'natural_disaster' => 'bg-purple-600 text-white',
            default            => 'bg-gray-600 text-white',
        };
    }
}
