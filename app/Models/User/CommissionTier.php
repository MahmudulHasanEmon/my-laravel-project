<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommissionTier extends Model
{
    protected $fillable = [
        'transaction_limit_id',
        'min_monthly_amount',
        'rate',
    ];

    public function transactionLimit(): BelongsTo
    {
        return $this->belongsTo(TransactionLimit::class);
    }
}

