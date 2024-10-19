<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('accounts list page is restricted', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/accounts');

    $response->assertRedirect('/dashboard');
});

test('accounts list page is displayed', function () {
    $user = User::factory()
        ->authorized()
        ->create();

    $response = $this
        ->actingAs($user)
        ->get('/accounts');

    $response->assertOk();
});

test('account page is restricted', function () {
    $user = User::factory()
        ->create();

    $response = $this
        ->actingAs($user)
        ->get('/account/1');

    $response->assertRedirect('/dashboard');
});

test('account page is displayed', function () {
    $user = User::factory()
        ->authorized()
        ->create();

    $response = $this
        ->actingAs($user)
        ->get('/account/1');

    $response->assertOk();
});

test('account update is restricted', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/account/1', [
            'name' => 'Test Account',
            'email' => 'test@example.com',
            'active' => true,
        ]);

    $response
        ->assertRedirect('/dashboard');

    $this->assertDatabaseMissing('users', [
        'name' => 'Test Account',
    ]);
});

test('account can be updated', function () {
    $user = User::factory()
        ->authorized()
        ->create();

    $response = $this
        ->actingAs($user)
        ->patch('/account/1', [
            'name' => 'Test Account',
            'email' => 'test@example.com',
            'active' => false,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/accounts');

    $user->refresh();

    $this->assertDatabaseHas('users', [
        'name' => 'Test Account',
        'active' => false,
    ]);
});
