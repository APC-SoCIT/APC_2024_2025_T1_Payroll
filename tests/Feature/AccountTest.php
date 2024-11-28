<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('accounts list page is restricted', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('accounts'));

    $response->assertRedirect(route('dashboard'));
});

test('accounts list page is displayed', function () {
    $user = User::factory()
        ->authorized()
        ->create();

    $response = $this
        ->actingAs($user)
        ->get(route('accounts'));

    $response->assertOk();
});

test('account page is restricted', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('account.get', 1));

    $response->assertRedirect(route('dashboard'));
});

test('account page is displayed', function () {
    $user = User::factory()
        ->authorized()
        ->create();

    $response = $this
        ->actingAs($user)
        ->get(route('account.get', 1));

    $response->assertOk();
});

test('account update is restricted', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch(route('account.update', 1), [
            'name' => 'Test Account',
            'email' => 'test@example.com',
            'active' => true,
        ]);

    $response->assertRedirect(route('dashboard'));

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
        ->patch(route('account.update', 1), [
            'name' => 'Test Account',
            'email' => 'test@example.com',
            'active' => false,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertOk(route('account.get', 1));

    $user->refresh();

    $this->assertDatabaseHas('users', [
        'name' => 'Test Account',
        'active' => false,
    ]);
});
