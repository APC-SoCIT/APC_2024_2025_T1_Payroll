<?php

use App\Enums\AdditionId;
use App\Enums\DeductionId;
use App\Models\PayrollItem;
use App\Models\Cutoff;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('payroll item is restricted', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('payroll.getCurrentFromUser', 1));

    $response->assertRedirect('/dashboard');
});

test('payroll item is displayed', function () {
    $user = User::factory()
        ->authorized()
        ->create();

    $response = $this
        ->actingAs($user)
        ->get(route('payroll.getCurrentFromUser', 1));

    $response->assertOk();
});

test('current payroll item additions can be updated', function () {
    $user = User::factory()
        ->authorized()
        ->create();

    $response = $this
        ->actingAs($user)
        ->get(route('payroll.getCurrentFromUser', 1));

    $response->assertOk();

    $response = $this
        ->actingAs($user)
        // add wage adjustment deduction
        ->post(route('itemAddition.new', ['payrollItem' => 1, 'addition' => 2]));

    $response->assertOk();

    $response = $this
        ->actingAs($user)
        // first itemAddition is for automatically calculated pay
        ->patch(route('itemAddition.update', 2), [
            'amount' => 727,
        ]);

    $response->assertOk();

    $this->assertDatabaseHas('item_additions', [
        'amount' => 727,
    ]);
});

test('future payroll item additions can be updated', function () {
    $user = User::factory()
        ->authorized()
        ->create();

    Cutoff::create([
        'start_date' => Carbon::now()->addMonth(1)->toDateString(),
        'cutoff_date' => Carbon::now()->addMonth(2)->toDateString(),
        'end_date' => Carbon::now()->addMonth(2)->toDateString(),
        'month_end' => true,
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('payroll.get', [
            'cutoff' => 1,
            'user' => 1
        ]));

    $response->assertOk();

    $response = $this
        ->actingAs($user)
        // add deminimis addition
        ->post(route('itemAddition.new', [
            'payrollItem' => 1,
            'addition' => AdditionId::Deminimis->value,
        ]));

    $response->assertOk();

    $response = $this
        ->actingAs($user)
        // first itemAddition is for automatically calculated pay
        ->patch(route('itemAddition.update', 2), [
            'amount' => 727,
        ]);

    $this->assertDatabaseHas('item_additions', [
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
        // add random deduction
        ->post(route('itemDeduction.new', [
            'payrollItem' => 1,
            'deduction' => DeductionId::Sla->value,
        ]));

    $response->assertOk();

    $response = $this
        ->actingAs($user)
        // first few itemDeductions are tax and contributions
        ->patch(route('itemDeduction.update', 5), [
            'amount' => 727,
        ]);

    $response->assertOk();

    $this->assertDatabaseHas('item_deductions', [
        'amount' => 727,
    ]);
});

test('future payroll item deductions can be updated', function () {
    $user = User::factory()
        ->authorized()
        ->configure()
        ->create();

    Cutoff::create([
        'start_date' => Carbon::now()->addMonth(1)->toDateString(),
        'cutoff_date' => Carbon::now()->addMonth(2)->toDateString(),
        'end_date' => Carbon::now()->addMonth(2)->toDateString(),
        'month_end' => true,
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('payroll.get', [
            'cutoff' => 1,
            'user' => 1
        ]));

    $response->assertOk();

    $response = $this
        ->actingAs($user)
        // add random deduction
        ->post(route('itemDeduction.new', [
            'payrollItem' => 1,
            'deduction' => DeductionId::Sla->value,
        ]));

    $response->assertOk();

    $response = $this
        ->actingAs($user)
        // first few itemDeductions are tax and contributions
        ->patch(route('itemDeduction.update', 5), [
            'amount' => 727,
        ]);

    $response->assertOk();

    $this->assertDatabaseHas('item_deductions', [
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

    Cutoff::create([
        'start_date' => $start,
        'cutoff_date' => $cutoff,
        'end_date' => $end,
        'month_end' => true,
    ]);

    PayrollItem::create([
        'user_id' => 1,
        'cutoff_id' => 1,
    ]);

    $response = $this
        ->actingAs($user)
        ->post(route('itemAddition.new', ['payrollItem' => 1, 'addition' => 1]));

    $response->assertForbidden();

    $response = $this
        ->actingAs($user)
        ->post(route('itemDeduction.new', ['payrollItem' => 1, 'deduction' => 1]));

    $response->assertForbidden();

    $response = $this
        ->actingAs($user)
        ->post(route('itemAddition.new', ['payrollItem' => 1, 'addition' => 1]));

    $response->assertForbidden();
});
