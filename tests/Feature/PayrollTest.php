<?php

use App\Models\PayrollItem;
use App\Models\PayrollPeriod;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('payroll item is restricted', function () {
    $user = User::factory()
        ->configure()
        ->create();

    $response = $this
        ->actingAs($user)
        ->get(route('payroll.getCurrentFromUser', 1));

    $response->assertRedirect('/dashboard');
});

test('payroll item is displayed', function () {
    $user = User::factory()
        ->authorized()
        ->configure()
        ->create();

    $response = $this
        ->actingAs($user)
        ->get(route('payroll.getCurrentFromUser', 1));

    $response->assertOk();
});

test('current payroll item additions can be updated', function () {
    $user = User::factory()
        ->authorized()
        ->configure()
        ->create();

    $response = $this
        ->actingAs($user)
        ->get(route('payroll.getCurrentFromUser', 1));

    $response->assertOk();

    $response = $this
        ->actingAs($user)
        ->post(route('additionItem.new', ['payrollItem' => 1, 'addition' => 1]));

    $response->assertRedirect(route('payroll.get', ['cutoff' => 1, 'user' => 1]));

    $response = $this
        ->actingAs($user)
        ->patch(route('additionItem.update', 1), [
            'amount' => 727,
        ]);

    $response->assertRedirect(route('payroll.get', ['cutoff' => 1, 'user' => 1]));

    $this->assertDatabaseHas('addition_items', [
        'amount' => 727,
    ]);
});

test('current payroll item base pay can be updated', function () {
    $user = User::factory()
        ->authorized()
        ->configure()
        ->create();

    $response = $this
        ->actingAs($user)
        ->get(route('payroll.getCurrentFromUser', 1));

    $response->assertOk();

    $response = $this
        ->actingAs($user)
        ->patch(route('userVariableItem.update', 1), [
            'value' => 500,
        ]);

    $response->assertRedirect('/account/1');

    $this->assertDatabaseHas('user_variable_items', [
        'value' => 500,
    ]);

    $response = $this
        ->actingAs($user)
        ->followingRedirects()
        ->patch(route('additionVariableItem.update', 2), [
            'value' => 75,
        ]);

    $this->assertDatabaseHas('addition_variable_items', [
        'value' => 75,
    ]);

    $this->assertDatabaseHas('addition_items', [
        'amount' => 37500,
    ]);
});

test('future payroll item additions can be updated', function () {
    $user = User::factory()
        ->authorized()
        ->configure()
        ->create();

    PayrollPeriod::create([
        'start_date' => Carbon::now()->addMonth(1)->toDateString(),
        'cutoff_date' => Carbon::now()->addMonth(2)->toDateString(),
        'end_date' => Carbon::now()->addMonth(2)->toDateString(),
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('payroll.getCurrentFromUser', 1));

    $response->assertOk();

    $response = $this
        ->actingAs($user)
        ->post(route('additionItem.new', ['payrollItem' => 1, 'addition' => 1]));

    $response->assertRedirect(route('payroll.get', ['cutoff' => 2, 'user' => 1]));

    $response = $this
        ->actingAs($user)
        ->patch(route('additionItem.update', 1), [
            'amount' => 727,
        ]);

    $response->assertRedirect(route('payroll.get', ['cutoff' => 2, 'user' => 1]));

    $this->assertDatabaseHas('addition_items', [
        'amount' => 727,
    ]);
});

test('payroll item deductions can be updated', function () {
    $user = User::factory()
        ->authorized()
        ->configure()
        ->create();

    $response = $this
        ->actingAs($user)
        ->get(route('payroll.getCurrentFromUser', 1));

    $response->assertOk();

    $response = $this
        ->actingAs($user)
        ->post(route('deductionItem.new', ['payrollItem' => 1, 'deduction' => 1]));

    $response->assertRedirect(route('payroll.get', ['cutoff' => 1, 'user' => 1]));

    $response = $this
        ->actingAs($user)
        ->patch(route('deductionItem.update', 1), [
            'amount' => 727,
        ]);

    $response->assertRedirect(route('payroll.get', ['cutoff' => 1, 'user' => 1]));

    $this->assertDatabaseHas('deduction_items', [
        'amount' => 727,
    ]);
});

test('future payroll item deductions can be updated', function () {
    $user = User::factory()
        ->authorized()
        ->configure()
        ->create();

    PayrollPeriod::create([
        'start_date' => Carbon::now()->addMonth(1)->toDateString(),
        'cutoff_date' => Carbon::now()->addMonth(2)->toDateString(),
        'end_date' => Carbon::now()->addMonth(2)->toDateString(),
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('payroll.getCurrentFromUser', 1));

    $response->assertOk();

    $response = $this
        ->actingAs($user)
        ->post(route('deductionItem.new', ['payrollItem' => 1, 'deduction' => 1]));

    $response->assertRedirect(route('payroll.get', ['cutoff' => 2, 'user' => 1]));

    $response = $this
        ->actingAs($user)
        ->patch(route('deductionItem.update', 1), [
            'amount' => 727,
        ]);

    $response->assertRedirect(route('payroll.get', ['cutoff' => 2, 'user' => 1]));

    $this->assertDatabaseHas('deduction_items', [
        'amount' => 727,
    ]);
});

test('previous payroll items updates are restricted', function () {
    $user = User::factory()
        ->authorized()
        ->configure()
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
        ->post(route('additionItem.new', ['payrollItem' => 1, 'addition' => 1]));

    $response->assertForbidden();

    $response = $this
        ->actingAs($user)
        ->post(route('deductionItem.new', ['payrollItem' => 1, 'deduction' => 1]));

    $response->assertForbidden();

    $response = $this
        ->actingAs($user)
        ->post(route('additionItem.new', ['payrollItem' => 1, 'addition' => 1]));

    $response->assertForbidden();
});
