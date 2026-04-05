<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PollOption extends Model
{
    protected $fillable = ['poll_id', 'option_text', 'option_order', 'vote_count'];

    public function poll(): BelongsTo
    {
        return $this->belongsTo(Poll::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(PollVote::class);
    }

    public function getPercentageAttribute(): float
    {
        $total = $this->poll->getTotalVotes();
        if ($total === 0) return 0;
        return round(($this->vote_count / $total) * 100, 1);
    }
}
