<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'plan_id',
        'tx_ref',
        'flutterwave_id',
        'price',
        'currency',
        'status',
        'raw_response',
    ];

    protected $casts = [
        'raw_response' => 'array',
        'price' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
