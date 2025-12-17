<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    //
    use HasFactory;


    protected $fillable = [
        'name',
        'price',
        'currency',
        'description',
        'is_active',
    ];

    protected $casts = [
        'price' => 'integer',
        'is_active' => 'boolean',
    ];

    public function user(): HasMany
    {
        return $this->hasMany(User::class);
    }
    public function transaction(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
