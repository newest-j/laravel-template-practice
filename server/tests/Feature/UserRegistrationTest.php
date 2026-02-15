<?php

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;


test('user can register succefully', function () {
    $response = $this->postJson('/register', [
        'name' => 'Jane Doe',
        'email' => 'janedoe@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);
    $response->assertStatus(201);
    $this->assertDatabaseHas('users', [
        'email' => 'janedoe@example.com',
        'name' => 'Jane Doe',
    ]);

    $user = User::where('email', 'janedoe@example.com')->first();
    expect(Hash::check('Password123!', $user->password))->toBeTrue();
    $this->assertAuthenticated();
});

test('registration failed with an invalid email', function () {
    $response = $this->postJson('/register', [
        'name' => 'Jane Doe',
        'email' => 'not-an-email',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);
    $response->assertStatus(422);
    $response->assertJsonValidationErrors('email');
});
