<?php

namespace App\Models;

use App\Enums\VisitorPassStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class VisitorPass extends Model
{
    protected $fillable = [
        'resident_id',
        'visitor_name',
        'visitor_phone',
        'vehicle_number',
        'purpose',
        'expected_date',
        'expected_from',
        'expected_to',
        'pass_code',
        'status',
        'approved_by',
        'checked_in_at',
        'checked_out_at',
        'notes',
    ];

    protected $casts = [
        'expected_date'  => 'date',
        'checked_in_at'  => 'datetime',
        'checked_out_at' => 'datetime',
        'status'         => VisitorPassStatus::class,
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $pass) {
            do {
                $code = 'VP-' . strtoupper(Str::random(5));
            } while (self::where('pass_code', $code)->exists());

            $pass->pass_code = $code;
        });
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function resident(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resident_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeTodayExpected($query)
    {
        return $query->whereDate('expected_date', today());
    }

    public function scopeUpcoming($query)
    {
        return $query->whereDate('expected_date', '>=', today());
    }

    public function scopePending($query)
    {
        return $query->where('status', VisitorPassStatus::Pending);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            VisitorPassStatus::Pending,
            VisitorPassStatus::Approved,
            VisitorPassStatus::CheckedIn,
        ]);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function isCancellable(): bool
    {
        return in_array($this->status, [VisitorPassStatus::Pending, VisitorPassStatus::Approved]);
    }
}
