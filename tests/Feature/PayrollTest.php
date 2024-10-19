<?php

use App\Models\PayrollItem;
use App\Models\PayrollPeriod;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('payroll item is restricted', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/payroll/account/' . $user->id);

    $response->assertRedirect('/dashboard');
});

test('payroll item is displayed', function () {
    $user = User::factory()
        ->authorized()
        ->create();

    $response = $this
        ->actingAs($user)
        ->get('/payroll/account/1');

    $response->assertOk();
});

test('payroll item additions can be updated', function() {
    $user = User::factory()
        ->authorized()
        ->create();

    $response = $this
        ->actingAs($user)
        ->get('/payroll/account/1');

    $response->assertOk();

    $response = $this
        ->actingAs($user)
        ->post('/payroll/additionItem/1/1');

    $response->assertRedirect('/payroll/account/1');

    $response = $this
        ->actingAs($user)
        ->patch('/payroll/additionItem/1', [
            'amount' => 727,
        ]);

    $response->assertRedirect('/payroll/account/1');

    $this->assertDatabaseHas('addition_items', [
        'amount' => 727,
    ]);
});

test('payroll item deductions can be updated', function() {
    $user = User::factory()
        ->authorized()
        ->create();

    $response = $this
        ->actingAs($user)
        ->get('/payroll/account/1');

    $response->assertOk();

    $response = $this
        ->actingAs($user)
        ->post('/payroll/deductionItem/1/1');

    $response->assertRedirect('/payroll/account/1');

    $response = $this
        ->actingAs($user)
        ->patch('/payroll/deductionItem/1', [
            'amount' => 727,
        ]);

    $response->assertRedirect('/payroll/account/1');

    $this->assertDatabaseHas('deduction_items', [
        'amount' => 727,
    ]);
});

test('previous payroll items updates are restricted', function() {
    $user = User::factory()
        ->authorized()
        ->create();

    $start = Carbon::now()->subMonth(2)->toDateString();
    $cutoff = Carbon::now()->subMonth(1)->toDateString();
    $end = $cutoff;

    PayrollPeriod::create([
        'start_date' => $start,
        'cutoff_date' => $cutoff,
        'end_date' => $end,
    ]);

    PayrollItem::create([
        'user_id' => 1,
        'payroll_period_id' => 1,
    ]);

    $response = $this
        ->actingAs($user)
        ->post('/payroll/additionItem/1/1');

    $response->assertForbidden();

    $response = $this
        ->actingAs($user)
        ->post('/payroll/deductionItem/1/1');

    $response = $this
        ->actingAs($user)
        ->post('/payroll/additionItem/1/1');
});
