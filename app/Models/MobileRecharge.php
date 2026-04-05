<?php

namespace App\Models;

use App\Models\Admin\Admin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MobileRecharge extends Model
{
    use HasFactory;

    protected $table = 'mobile_recharges';

    protected $fillable = [
        'type',
        'operator',
        'request_type',
        'block_amount',
        'pending_amount',
        'ussd',
        'balance',
        'is_pending',
        'is_active',
        'minimum_amount',
        'maximum_amount',
        'is_offer_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'block_amount' => 'array',
        'pending_amount' => 'array',
        
        'balance' => 'decimal:2',
        'minimum_amount' => 'decimal:2',
        'maximum_amount' => 'decimal:2',
        'is_pending' => 'boolean',
        'is_active' => 'boolean',
        'is_offer_active' => 'boolean',
    ];

    public function createdBy()
    {
        return $this->belongsTo(Admin::class, 'created_by', 'admin_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(Admin::class, 'updated_by', 'admin_id');
    }
}