<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    
    protected $table = 'payment_gateways';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'request',
        'method',
        'item_type',
        'gateway_type',
        'phone',
        'account_number',
        'holder_name',
        'address',
        'username',
        'password',
        'app_key',
        'secret_key',
        'minimum_amount',
        'maximum_amount',
        'is_active',
        'remarks',
        'fees',
        'tax',
        'commission',
        'processing_time',
        'logo_url',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'minimum_amount' => 'decimal:2',
        'maximum_amount' => 'decimal:2',
        'tax' => 'decimal:2',
        'commission' => 'decimal:2',
        'fees' => 'decimal:2',
        'is_active' => 'boolean',
    ];
}