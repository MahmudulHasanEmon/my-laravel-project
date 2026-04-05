<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionLimit extends Model
{
    use HasFactory;

    protected $table = 'transaction_limits';

    protected $fillable = [
        'role_id',
        'trx_type',
        'daily_count',
        'daily_amount',
        'monthly_count',
        'monthly_amount',
        'per_transaction_min',
        'per_transaction_max',
        'charge_type',
        'charge_value',
        
        'trx_fee',
        'trx_free_count',

        'commission_type',
        'commission_value',
    ];

    /**
     * ✅ Type casting
     */
    protected $casts = [
        'daily_amount' => 'decimal:2',
        'monthly_amount' => 'decimal:2',
        'per_transaction_min' => 'decimal:2',
        'per_transaction_max' => 'decimal:2',
        'charge_value' => 'decimal:2',
        'commission_value' => 'decimal:2',
    ];

    /**
     * 🔗 Relationship: Role
     */
    public function role()
    {
        return $this->belongsTo(UserRole::class, 'role_id');
    }

}
