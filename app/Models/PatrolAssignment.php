<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatrolAssignment extends Model
{
    protected $fillable = [
        'assigned_by', 'assigned_to', 'area', 'shift_start', 'shift_end', 'notes', 'status',
    ];

    protected function casts(): array
    {
        return [
            'shift_start' => 'datetime',
            'shift_end'   => 'datetime',
        ];
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
