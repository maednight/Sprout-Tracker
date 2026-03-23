<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'category_id',
        'account_id',
        'occurred_at',
        'description',
        'receipt_photo_path',
        'receipt_photo_paths',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'amount' => 'decimal:2',
        'receipt_photo_paths' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function savingsTransfer(): HasOne
    {
        return $this->hasOne(SavingsTransfer::class, 'income_transaction_id');
    }

    public function destinationSavingsTransfer(): HasOne
    {
        return $this->hasOne(SavingsTransfer::class, 'savings_transaction_id');
    }
}
