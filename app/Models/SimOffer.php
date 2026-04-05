<?php

namespace App\Models;

use App\Models\Admin\Admin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SimOffer extends Model
{
    use HasFactory;

    protected $table = 'sim_offers';

    /**
     * Mass assignable fields
     */
    protected $fillable = [
        'item_type',
        'type',
        'operator',
        'title',
        'code',
        'validity',
        'price',
        'cashback',
        'discount',
        'details',
        'tag',
        'activation_method',
        'eligibility',
        'terms_conditions',
        'status',
        'cashback_start_at',
        'cashback_hold',
        'cashback_end_at',
        'offer_start_at',
        'offer_end_at',
        'offer_stock',
        
        'created_by',
        'updated_by',
    ];

    /**
     * Attribute casting
     */
    protected $casts = [
        'price' => 'decimal:2',
        'cashback' => 'decimal:2',
        'discount' => 'decimal:2',
        'cashback_hold' => 'decimal:2',

        'cashback_start_at' => 'datetime',
        'cashback_end_at' => 'datetime',
        'active_start_at' => 'datetime',
        'active_end_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function createdBy()
    {
        return $this->belongsTo(Admin::class, 'created_by', 'admin_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(Admin::class, 'updated_by', 'admin_id');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOperator($query, $operator)
    {
        return $query->where('operator', $operator);
    }

    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }
}