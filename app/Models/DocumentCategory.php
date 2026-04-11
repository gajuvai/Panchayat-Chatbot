<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentCategory extends Model
{
    protected $fillable = ['name', 'icon', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
