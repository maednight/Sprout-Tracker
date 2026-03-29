<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Represents a budget for a user and period.
 */
class Budget extends Model
{
    /**
     * @var array<int, string> Attributes allowed for mass assignment.
     */
    protected $fillable = [
        'user_id',
        'name',
        'cycle',
        'is_reused',
        'period_date',
    ];

    /**
     * @var array<string, string> Attribute cast rules applied by the model.
     */
    protected $casts = [
        'is_reused' => 'boolean',
        'period_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BudgetItem::class);
    }
}
