<?php

namespace App\Models;

use App\Enums\ComplaintPriority;
use App\Enums\ComplaintStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Complaint extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'complaint_number', 'user_id', 'category_id', 'title', 'description',
        'status', 'priority', 'location', 'assigned_to', 'assigned_at',
        'resolved_at', 'resolution_notes', 'is_anonymous', 'upvotes',
    ];

    protected function casts(): array
    {
        return [
            'status'      => ComplaintStatus::class,
            'priority'    => ComplaintPriority::class,
            'is_anonymous'=> 'boolean',
            'assigned_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($complaint) {
            $count = static::withTrashed()->count() + 1;
            $complaint->complaint_number = 'CMP-' . date('Y') . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ComplaintCategory::class, 'category_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function media(): HasMany
    {
        return $this->hasMany(ComplaintMedia::class);
    }

    public function updates(): HasMany
    {
        return $this->hasMany(ComplaintUpdate::class);
    }

    public function feedback(): HasOne
    {
        return $this->hasOne(Feedback::class);
    }

    public function upvoteRecords(): HasMany
    {
        return $this->hasMany(ComplaintUpvote::class);
    }

    public function securityIncident(): HasOne
    {
        return $this->hasOne(SecurityIncident::class);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    public function scopeByCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeAssignedTo($query, int $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function isUpvotedBy(User $user): bool
    {
        return $this->upvoteRecords()->where('user_id', $user->id)->exists();
    }
}
