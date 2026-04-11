<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Expense extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'category',
        'amount',
        'expense_date',
        'vendor',
        'invoice_number',
        'receipt_path',
        'is_recurring',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount'       => 'decimal:2',
        'is_recurring' => 'boolean',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function loggedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ─── Accessors ────────────────────────────────────────────────────────────

    public function getReceiptUrlAttribute(): ?string
    {
        return $this->receipt_path ? Storage::disk('public')->url($this->receipt_path) : null;
    }

    public function getCategoryLabelAttribute(): string
    {
        return ucfirst(str_replace('_', ' ', $this->category));
    }

    public function getCategoryIconAttribute(): string
    {
        return match($this->category) {
            'maintenance'    => '🔧',
            'security'       => '🔒',
            'utilities'      => '💡',
            'events'         => '🎉',
            'staff_salary'   => '👤',
            'cleaning'       => '🧹',
            'landscaping'    => '🌿',
            'administrative' => '📋',
            'emergency'      => '🚨',
            default          => '📦',
        };
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeForYear($query, int $year)
    {
        return $query->whereYear('expense_date', $year);
    }

    public function scopeForMonth($query, int $year, int $month)
    {
        return $query->whereYear('expense_date', $year)
                     ->whereMonth('expense_date', $month);
    }

    // ─── Static Aggregators ───────────────────────────────────────────────────

    /**
     * Monthly totals for a given year: [1 => 5000, 2 => 3200, ...]
     */
    public static function monthlyTotals(int $year): array
    {
        $rows = self::forYear($year)
            ->selectRaw('EXTRACT(MONTH FROM expense_date)::int as month, SUM(amount) as total')
            ->groupByRaw('EXTRACT(MONTH FROM expense_date)')
            ->pluck('total', 'month')
            ->toArray();

        $result = [];
        for ($m = 1; $m <= 12; $m++) {
            $result[$m] = (float) ($rows[$m] ?? 0);
        }
        return $result;
    }

    /**
     * Category totals for a given year: ['maintenance' => 5000, ...]
     */
    public static function categoryTotals(int $year): array
    {
        return self::forYear($year)
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->pluck('total', 'category')
            ->map(fn ($v) => (float) $v)
            ->toArray();
    }

    public static function categories(): array
    {
        return [
            'maintenance','security','utilities','events','staff_salary',
            'cleaning','landscaping','administrative','emergency','other',
        ];
    }
}
