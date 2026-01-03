<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;




class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'google_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function subcription(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }


    public function createSubscription(Transaction $transaction): Subscription
    {
        return $this->subscriptions()->create([
            'plan_id' => $transaction->plan_id,
            'tx_ref' => $transaction->tx_ref,
            'flutterwave_id' => $transaction->flutterwave_id,
            'price' => $transaction->price,
            'currency' => $transaction->currency,
            'status' => 'active',
            'started_at' => now(),
            'expires_at' => now()->addMonth(),
            'raw_response' => $transaction->raw_response,
        ]);
    }
}
