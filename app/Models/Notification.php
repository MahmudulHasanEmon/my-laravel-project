<?php

namespace App\Models;

use App\Models\Admin\Admin;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    protected $fillable = [
        'user_id',
        'recipient_type',
        'title',
        'body',
        'image_url',
        'type',
        'channel',
        'action_url',
        'priority',
        'sender',
        'seen',
        'delivered',
        'expire_at',
        'created_by',
        'updated_by',
    ];
    
    protected $casts = [
        'seen' => 'boolean',
        'delivered' => 'boolean',
        'expire_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    
    public function scopeUserSlider(Builder $query, $userId): Builder
    {
        return $query->where('channel', 'SLIDER')
            ->whereIn('recipient_type', ['user', 'all']) // recipient_type অবশ্যই user অথবা all হতে হবে
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId) // ইউজারের নিজের আইডি মিলবে
                    ->orWhereNull('user_id');   // অথবা user_id ফাকা (NULL) থাকতে পারে (যদি সবার জন্য হয়)
            });
    }

    public function updatedByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'updated_by', 'admin_id');
    }
}