<?php

// use Illuminate\Foundation\Testing\RefreshDatabase;
// with the feture test we can talk to the database route and even use the full framework 
// we can test the route controller validation auth response


test('the application returns a successful response', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});
