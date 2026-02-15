<?php


// this unit test is just a test for a single method, small logic function, class
// does not talk to the database, route or use the full framework
// so the unit test is just testing the behaviours of class or function not saving to DB or others
// public function isActive(): bool
// {
//     return $this->status === 'active' &&
//            $this->expires_at &&
//            $this->expires_at->isFuture();
// }

// A unit test will test only that method.

// public function test_subscription_is_active()
// {
//     $subscription = new Subscription([
//         'status' => 'active',
//         'expires_at' => now()->addDay(),
//     ]);

//     $this->assertTrue($subscription->isActive());
// }

test('that true is true', function () {
    expect(true)->toBeTrue();
});
