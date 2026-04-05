<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{

    use HasFactory;

    protected $table = 'transactions';
    
    // 🔹 Mass assignable fields
    protected $fillable = [
        'user_id',
        'type',
        'trx_id',
        'trx_type',
        'amount',
        'fee',
        'charge',
        'discount',
        'currency',
        'balance_before',
        'balance_after',
        'trx_ref',
        'receiver',
        'receiver_meta',
        'status',
        'approved_by',
        'approved_at',
        'is_refundable',
        'is_flagged',
        'ip_address',
        'device_info',
        'remarks',
        'created_by',
        'updated_by',
    ];

    // 🔹 Casts (very important for fintech)
    protected $casts = [
        'amount' => 'decimal:4',
        'fee' => 'decimal:4',
        'charge' => 'decimal:4',
        'discount' => 'decimal:4',
        'balance_before' => 'decimal:4',
        'balance_after' => 'decimal:4',
        'receiver_meta' => 'array',
        'is_refundable' => 'boolean',
        'is_flagged' => 'boolean',
        'approved_at' => 'datetime',
    ];

    // 🔹 Relationships

    // User (owner of transaction)
    public function user()
    {
        return $this->belongsTo(\App\Models\User\User::class, 'user_id');
    }

    // Created by Admin
    public function creator()
    {
        return $this->belongsTo(\App\Models\Admin\Admin::class, 'created_by', 'admin_id');
    }
    
    // Updated by Admin
    public function updater()
    {
        return $this->belongsTo(\App\Models\Admin\Admin::class, 'updated_by', 'admin_id');
    }


    // Approved by Admin
    public function approver()
    {
        return $this->belongsTo(\App\Models\Admin\Admin::class, 'approved_by', 'admin_id');
    }

    // 🔹 Scopes (clean query use)

    public function scopeCredit($query)
    {
        return $query->where('type', 'credit');
    }

    public function scopeDebit($query)
    {
        return $query->where('type', 'debit');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
