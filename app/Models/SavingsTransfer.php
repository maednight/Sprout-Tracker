<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavingsTransfer extends Model
{
    protected $fillable = [
        'user_id',
        'source_category_id',
        'destination_category_id',
        'account_id',
        'savings_transaction_id',
        'income_transaction_id',
        'amount',
        'transferred_at',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transferred_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sourceCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'source_category_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function destinationCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'destination_category_id');
    }

    public function savingsTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'savings_transaction_id');
    }

    public function incomeTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'income_transaction_id');
    }
}
