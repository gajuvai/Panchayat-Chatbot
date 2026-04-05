<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Announcement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'title', 'body', 'type', 'target_role',
        'is_published', 'published_at', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'expires_at'   => 'datetime',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
        });
    }

    public function scopeForRole($query, ?string $role)
    {
        return $query->where(function ($q) use ($role) {
            $q->whereNull('target_role')->orWhere('target_role', $role);
        });
    }

    public function typeBadgeClass(): string
    {
        return match($this->type) {
            'urgent'      => 'bg-red-100 text-red-800',
            'maintenance' => 'bg-yellow-100 text-yellow-800',
            'event'       => 'bg-purple-100 text-purple-800',
            default       => 'bg-blue-100 text-blue-800',
        };
    }
}
