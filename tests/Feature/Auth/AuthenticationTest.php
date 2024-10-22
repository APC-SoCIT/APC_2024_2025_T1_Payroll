<?php

use App\Models\User;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

test('login screen can be rendered', function () {
    $response = $this->call('GET', URL::to('/auth/redirect'));

    $response->assertRedirect();
});

test('users can authenticate using socialite', function () {
    $abstractUser = Mockery::mock('Laravel\Socialite\Two\User');
    $abstractUser->name = Str::random(10);
    $abstractUser->mail = Str::random(10).'@apc.edu.ph';

    Socialite::shouldReceive('driver->user')->andReturn($abstractUser);
    $response = $this->get('/auth/callback');
    $response->assertRedirect(route('dashboard'));
});

test('users can logout', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/logout');

    $this->assertGuest();
});
