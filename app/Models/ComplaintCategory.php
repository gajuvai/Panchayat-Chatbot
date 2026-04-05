<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComplaintCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'icon', 'description', 'assigned_role_id', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function assignedRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'assigned_role_id');
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class, 'category_id');
    }

    public function forumThreads(): HasMany
    {
        return $this->hasMany(ForumThread::class, 'category_id');
    }
}
