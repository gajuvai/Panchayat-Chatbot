<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    protected $fillable = [
        'session_id', 'sender', 'message', 'message_type', 'intent', 'metadata', 'is_read',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'is_read'  => 'boolean',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(ChatSession::class, 'session_id');
    }
}
