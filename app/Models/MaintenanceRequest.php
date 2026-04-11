<?php

namespace App\Models;

use App\Enums\MaintenanceStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'request_number',
        'requested_by',
        'assigned_to',
        'category_id',
        'title',
        'description',
        'location',
        'status',
        'priority',
        'vendor_name',
        'vendor_contact',
        'estimated_cost',
        'actual_cost',
        'scheduled_at',
        'completed_at',
        'completion_notes',
        'rejection_reason',
    ];

    protected $casts = [
        'status'       => MaintenanceStatus::class,
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $req) {
            $year  = date('Y');
            $count = self::withTrashed()->whereYear('created_at', $year)->count() + 1;
            $req->request_number = 'MNT-' . $year . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);
        });
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ComplaintCategory::class, 'category_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(MaintenanceMedia::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', MaintenanceStatus::Pending);
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', [
            MaintenanceStatus::Completed,
            MaintenanceStatus::Rejected,
            MaintenanceStatus::Cancelled,
        ]);
    }
}
