<?php

use App\Models\Cutoff;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('payroll cutoff list is restricted', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/cutoffs');

    $response->assertRedirect('/dashboard');
});

test('payroll cutoff list is displayed', function () {
    $user = User::factory()
        ->authorized()
        ->create();

    $response = $this
        ->actingAs($user)
        ->get('/cutoffs');

    $response->assertOk();
});

test('payroll cutoff can be created', function () {
    $user = User::factory()
        ->authorized()
        ->create();

    $response = $this
        ->actingAs($user)
        ->get('/cutoff/new');

    $response->assertOk();

    $start = Carbon::now()->toDateString();
    $cutoff = Carbon::now()->addMonth(2)->toDateString();
    $end = $cutoff;

    $response = $this
        ->actingAs($user)
        ->post('/cutoff/new', [
            'start_date' => $start,
            'cutoff_date' => $cutoff,
            'end_date' => $end,
        ]);

    $response->assertRedirect('/cutoffs');
    $this->assertDatabaseHas('cutoffs', [
        'start_date' => $start,
        'cutoff_date' => $cutoff,
        'end_date' => $end,
    ]);
});

test('payroll cutoff creation validation', function () {
    $user = User::factory()
        ->authorized()
        ->create();

    $response = $this
        ->actingAs($user)
        ->get('/cutoff/new');

    $response->assertOk();

    // end before today
    $start = Carbon::now()->subMonth(2)->toDateString();
    $cutoff = Carbon::now()->subMonth(1)->toDateString();
    $end = $cutoff;

    $response = $this
        ->actingAs($user)
        ->post('/cutoff/new', [
            'start_date' => $start,
            'cutoff_date' => $cutoff,
            'end_date' => $end,
        ]);

    $response->assertInvalid(['end_date']);

    // end before start
    $start = Carbon::now()->addMonth(3)->toDateString();
    $cutoff = Carbon::now()->addMonth(2)->toDateString();
    $end = $cutoff;

    $response = $this
        ->actingAs($user)
        ->post('/cutoff/new', [
            'start_date' => $start,
            'cutoff_date' => $cutoff,
            'end_date' => $end,
        ]);

    $response->assertInvalid(['end_date']);

    // cutoff out of range
    $start = Carbon::now()->toDateString();
    $cutoff = Carbon::now()->addMonth(1)->toDateString();
    $end = $start;

    $response = $this
        ->actingAs($user)
        ->post('/cutoff/new', [
            'start_date' => $start,
            'cutoff_date' => $cutoff,
            'end_date' => $end,
        ]);

    $response->assertInvalid(['cutoff_date']);
});

test('payroll cutoff update validation', function () {
    $user = User::factory()
        ->authorized()
        ->create();

    $start = Carbon::now()->subMonth(2)->toDateString();
    $cutoff = Carbon::now()->subMonth(1)->toDateString();
    $end = $cutoff;

    Cutoff::create([
        'start_date' => $start,
        'cutoff_date' => $cutoff,
        'end_date' => $end,
    ]);

    $response = $this
        ->actingAs($user)
        ->get('/cutoff/1');

    $response->assertOk();

    // end before today
    $start = Carbon::now()->subMonth(2)->toDateString();
    $cutoff = Carbon::now()->subMonth(1)->toDateString();
    $end = $cutoff;

    $response = $this
        ->actingAs($user)
        ->patch('/cutoff/1', [
            'start_date' => $start,
            'cutoff_date' => $cutoff,
            'end_date' => $end,
        ]);

    $response->assertInvalid(['end_date']);

    $start = Carbon::now()->addMonth(1)->toDateString();
    $cutoff = Carbon::now()->addMonth(2)->toDateString();
    $end = $cutoff;

    Cutoff::create([
        'start_date' => $start,
        'cutoff_date' => $cutoff,
        'end_date' => $end,
    ]);

    // end before start
    $start = Carbon::now()->addMonth(3)->toDateString();
    $cutoff = Carbon::now()->addMonth(2)->toDateString();
    $end = $cutoff;

    $response = $this
        ->actingAs($user)
        ->patch('/cutoff/2', [
            'start_date' => $start,
            'cutoff_date' => $cutoff,
            'end_date' => $end,
        ]);

    $response->assertInvalid(['end_date']);

    // cutoff out of range
    $start = Carbon::now()->toDateString();
    $cutoff = Carbon::now()->addMonth(1)->toDateString();
    $end = $start;

    $response = $this
        ->actingAs($user)
        ->patch('/cutoff/2', [
            'start_date' => $start,
            'cutoff_date' => $cutoff,
            'end_date' => $end,
        ]);

    $response->assertInvalid(['cutoff_date']);
});
