<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecurityIncident extends Model
{
    protected $fillable = [
        'complaint_id', 'reported_by', 'incident_type', 'description',
        'location', 'occurred_at', 'status', 'severity',
    ];

    protected function casts(): array
    {
        return ['occurred_at' => 'datetime'];
    }

    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function severityBadgeClass(): string
    {
        return match($this->severity) {
            'critical' => 'bg-red-200 text-red-900',
            'high'     => 'bg-orange-100 text-orange-800',
            'medium'   => 'bg-yellow-100 text-yellow-800',
            default    => 'bg-gray-100 text-gray-700',
        };
    }
}
