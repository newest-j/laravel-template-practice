<?php

use App\Models\Subscription;
use Carbon\Carbon;

beforeEach(function () {
    Carbon::setTestNow(now());
});

// i am using this public declaring state here of the new class the anonymous class 
// instead of the normal $subscription = new Subscription([]) because 
// the unit test is not to connect to the database and the cast

//     protected $casts = [
//         'started_at' => 'datetime',
//         'expires_at' => 'datetime',
//         'raw_response' => 'array',
//     ];
// reading data from the database want to connect to the database

it('return true when subscription is active and not expired', function () {
    $subscription = new class extends Subscription {
        public $status;
        public $started_at;
        public $expires_at;
    };

    $subscription->status = 'active';
    $subscription->started_at = now();
    $subscription->expires_at = now()->addDays(5);

    expect($subscription->isActive())->toBeTrue();
});

it('return false when status is inactive', function () {
    $subscription = new class extends Subscription {
        public $status;
        public $started_at;
        public $expires_at;
    };

    $subscription->status = 'inactive';
    $subscription->started_at = now();
    $subscription->expires_at = now()->addDays(5);

    expect($subscription->isActive())->toBeFalse();
});

it('return false when expired date is null', function () {
    $subscription = new class extends Subscription {
        public $status;
        public $started_at;
        public $expires_at;
    };

    $subscription->status = 'active';
    $subscription->started_at = now();
    $subscription->expires_at = null;

    expect($subscription->isActive())->toBeFalse();
});
