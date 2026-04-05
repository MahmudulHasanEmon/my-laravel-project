<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Token extends Model
{
    use HasFactory;

    /**
     * যেসব ফিল্ড mass assignment এ fillable হবে
     */
    protected $fillable = [
        'phone',
        'jti',
        'device_id',
        'fingerprint',
        'expires_at',
        'revoked',
        'tokenable_id',
        'tokenable_type',
    ];

    /**
     * Casting (ডাটাবেস থেকে আসা ভ্যালু automatic type এ কনভার্ট হবে)
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'revoked' => 'boolean',
    ];

    /**
     * Polymorphic relation
     * Token -> User বা Admin
     */
    public function tokenable()
    {
        return $this->morphTo();
    }
}
