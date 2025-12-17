<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
}
