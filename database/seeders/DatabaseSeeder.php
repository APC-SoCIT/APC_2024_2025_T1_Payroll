<?php

namespace Database\Seeders;

use App\Enums\AdditionId;
use App\Enums\DeductionId;
use App\Helpers\PayrollHelper;
use App\Models\Cutoff;
use App\Models\ItemAddition;
use App\Models\ItemDeduction;
use App\Models\PayrollItem;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->authorized()->create();
        User::factory(10)->create();

        $cutoffsDetails = [
            [
                'start_date' => '2024-01-01',
                'cutoff_date' => '2024-01-10',
                'end_date' => '2024-01-15',
                'month_end' => false,
            ], [
                'start_date' => '2024-01-16',
                'cutoff_date' => '2024-01-26',
                'end_date' => '2024-01-31',
                'month_end' => true,
            ], [
                'start_date' => '2024-02-01',
                'cutoff_date' => '2024-02-10',
                'end_date' => '2024-02-15',
                'month_end' => false,
            ], [
                'start_date' => '2024-02-16',
                'cutoff_date' => '2024-02-24',
                'end_date' => '2024-02-28',
                'month_end' => true,
            ], [
                'start_date' => '2024-03-01',
                'cutoff_date' => '2024-03-10',
                'end_date' => '2024-03-15',
                'month_end' => false,
            ], [
                'start_date' => '2024-03-16',
                'cutoff_date' => '2024-03-26',
                'end_date' => '2024-03-31',
                'month_end' => true,
            ], [
                'start_date' => '2024-04-01',
                'cutoff_date' => '2024-04-10',
                'end_date' => '2024-04-15',
                'month_end' => false,
            ], [
                'start_date' => '2024-04-16',
                'cutoff_date' => '2024-04-26',
                'end_date' => '2024-04-30',
                'month_end' => true,
            ], [
                'start_date' => '2024-05-01',
                'cutoff_date' => '2024-05-10',
                'end_date' => '2024-05-15',
                'month_end' => false,
            ], [
                'start_date' => '2024-05-16',
                'cutoff_date' => '2024-05-26',
                'end_date' => '2024-05-31',
                'month_end' => true,
            ], [
                'start_date' => '2024-06-01',
                'cutoff_date' => '2024-06-10',
                'end_date' => '2024-06-15',
                'month_end' => false,
            ], [
                'start_date' => '2024-06-16',
                'cutoff_date' => '2024-06-26',
                'end_date' => '2024-06-30',
                'month_end' => true,
            ], [
                'start_date' => '2024-07-01',
                'cutoff_date' => '2024-07-10',
                'end_date' => '2024-07-15',
                'month_end' => false,
            ], [
                'start_date' => '2024-07-16',
                'cutoff_date' => '2024-07-26',
                'end_date' => '2024-07-31',
                'month_end' => true,
            ], [
                'start_date' => '2024-08-01',
                'cutoff_date' => '2024-08-10',
                'end_date' => '2024-08-15',
                'month_end' => false,
            ], [
                'start_date' => '2024-08-16',
                'cutoff_date' => '2024-08-26',
                'end_date' => '2024-08-31',
                'month_end' => true,
            ], [
                'start_date' => '2024-09-01',
                'cutoff_date' => '2024-09-10',
                'end_date' => '2024-09-15',
                'month_end' => false,
            ], [
                'start_date' => '2024-09-16',
                'cutoff_date' => '2024-09-26',
                'end_date' => '2024-09-30',
                'month_end' => true,
            ], [
                'start_date' => '2024-10-01',
                'cutoff_date' => '2024-10-10',
                'end_date' => '2024-10-15',
                'month_end' => false,
            ]
        ];

        $cutoffs = [];
        foreach ($cutoffsDetails as $details) {
            array_push($cutoffs, Cutoff::create($details));
        }

        $users = User::all();
        foreach ($users as $user) {
            $item = PayrollItem::create(['cutoff_id' => $cutoffs[0]->id, 'user_id' => $user->id]);
            ItemAddition::create([
                'payroll_item_id' => $item->id,
                'addition_id' => AdditionId::Salary->value,
                'amount' => 20000,
            ]);
            ItemDeduction::create([
                'payroll_item_id' => $item->id,
                'deduction_id' => DeductionId::Pagibig->value,
                'amount' => 100,
            ]);
            ItemDeduction::create([
                'payroll_item_id' => $item->id,
                'deduction_id' => DeductionId::SssLoan->value,
                'amount' => 250,
                'remaining_payments' => 10,
            ]);
            $item->load(['itemAdditions', 'itemDeductions']);
        }

        foreach ($cutoffs as $cutoff) {
            foreach ($users as $user) {
                $item = PayrollItem::firstOrCreate(['cutoff_id' => $cutoff->id, 'user_id' => $user->id]);
                PayrollHelper::calculateAll($item);
            }
        }
    }
}
