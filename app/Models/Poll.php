<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Poll extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'title', 'description', 'poll_type',
        'starts_at', 'ends_at', 'is_anonymous', 'is_active', 'show_results_before_end',
    ];

    protected function casts(): array
    {
        return [
            'starts_at'               => 'datetime',
            'ends_at'                 => 'datetime',
            'is_anonymous'            => 'boolean',
            'is_active'               => 'boolean',
            'show_results_before_end' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(PollOption::class)->orderBy('option_order');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(PollVote::class);
    }

    public function scopeActiveNowScope($query)
    {
        return $query->where('is_active', true)->where('ends_at', '>', now());
    }

    public function hasUserVoted(User $user): bool
    {
        return $this->votes()->where('user_id', $user->id)->exists();
    }

    public function getTotalVotes(): int
    {
        return $this->votes()->count();
    }

    public function isActiveNow(): bool
    {
        return $this->is_active && now()->between($this->starts_at, $this->ends_at);
    }

    public function canShowResults(): bool
    {
        return $this->show_results_before_end || now()->isAfter($this->ends_at);
    }
}
