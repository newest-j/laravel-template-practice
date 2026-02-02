<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    //
    protected $fillable = [
        'user_id',
        'plan_id',
        'tx_ref',
        'flutterwave_id',
        'price',
        'currency',
        'started_at',
        'expires_at',
        'status',
        'raw_response',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'raw_response' => 'array',
    ];

    // Subscription belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Subscription belongs to a plan
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' &&
            $this->started_at &&
            $this->expires_at &&
            $this->expires_at->isFuture();
    }


    // Activate subscription
    public function activate(int $durationInDays = 30)
    {
        $this->started_at = now();
        $this->expires_at = now()->addDays($durationInDays);
        $this->status = 'active';
        $this->save();
    }

    // Cancel subscription
    public function cancel()
    {
        $this->status = 'cancelled';
        $this->save();
    }
}
