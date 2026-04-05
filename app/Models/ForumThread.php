<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ForumThread extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'category_id', 'title', 'body',
        'is_pinned', 'is_locked', 'is_approved', 'last_reply_at',
    ];

    protected function casts(): array
    {
        return [
            'is_pinned'    => 'boolean',
            'is_locked'    => 'boolean',
            'is_approved'  => 'boolean',
            'last_reply_at'=> 'datetime',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ComplaintCategory::class, 'category_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(ForumReply::class, 'thread_id');
    }

    public function topReplies(): HasMany
    {
        return $this->hasMany(ForumReply::class, 'thread_id')->whereNull('parent_id');
    }

    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }
}
