<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uploaded_by',
        'category_id',
        'title',
        'description',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'version',
        'access_level',
        'download_count',
    ];

    protected $casts = [
        'file_size'      => 'integer',
        'version'        => 'integer',
        'download_count' => 'integer',
    ];

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(DocumentCategory::class, 'category_id');
    }

    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
        return round($bytes / 1048576, 1) . ' MB';
    }

    public function getFileIconAttribute(): string
    {
        return match(true) {
            str_contains($this->mime_type, 'pdf')   => '📄',
            str_contains($this->mime_type, 'word')
                || str_contains($this->mime_type, 'docx')
                || str_contains($this->mime_type, 'document') => '📝',
            str_contains($this->mime_type, 'sheet')
                || str_contains($this->mime_type, 'excel')
                || str_contains($this->mime_type, 'spreadsheet') => '📊',
            str_contains($this->mime_type, 'image') => '🖼️',
            default                                 => '📎',
        };
    }

    public function isAccessibleBy(User $user): bool
    {
        return match($this->access_level) {
            'all'      => true,
            'resident' => in_array($user->role?->name, ['resident', 'admin']),
            'admin'    => $user->isAdmin(),
            default    => false,
        };
    }

    public function scopeAccessibleBy($query, User $user)
    {
        if ($user->isAdmin()) return $query;
        if ($user->isResident()) return $query->where('access_level', '!=', 'admin');
        return $query->where('access_level', 'all');
    }
}
