<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Represents a budget allocation line item.
 */
class BudgetItem extends Model
{
    /**
     * @var array<int, string> Attributes allowed for mass assignment.
     */
    protected $fillable = [
        'budget_id',
        'category_id',
        'category_name',
        'allocated_amount',
    ];

    /**
     * @var array<string, string> Attribute cast rules applied by the model.
     */
    protected $casts = [
        'allocated_amount' => 'decimal:2',
    ];

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
