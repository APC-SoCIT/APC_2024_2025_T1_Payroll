<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payroll_periods', function (Blueprint $table) {
            $table->id();
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();
        });

        Schema::create('payroll_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('payroll_period_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('additions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->boolean('calculated');
            $table->timestamps();
        });

        Schema::create('addition_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_item_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('addition_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->decimal('amount');
            $table->timestamps();
        });

        Schema::create('deductions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->boolean('calculated');
            $table->timestamps();
        });

        Schema::create('deduction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_item_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('deduction_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->decimal('amount');
            $table->timestamps();
        });

        $additions = [
            [
                'name' => 'Salary/Wage',
                'description' => 'Base pay based on contract (edit through account)',
                'calculated' => true,
            ], [
                'name' => 'Deminimis Benefits',
                'description' => 'Deminimis Benefits',
                'calculated' => false,
            ], [
                'name' => 'Professional Fee',
                'description' => 'Professional fees, including consultation and adviser fees',
                'calculated' => false,
            ], [
                'name' => 'Allowance',
                'description' => 'Allowance',
                'calculated' => false,
            ], [
                'name' => 'Honorarium',
                'description' => 'Honorarium',
                'calculated' => false,
            ], [
                'name' => 'Merit Increase',
                'description' => 'Merit Increase',
                'calculated' => false,
            ], [
                'name' => 'Allowance Adjustment',
                'description' => 'Allowance Adjustment',
                'calculated' => false,
            ], [
                'name' => 'Honorarium Others',
                'description' => 'Honorarium others',
                'calculated' => false,
            ], [
                'name' => 'Professional Fee Others',
                'description' => 'Professional fee others',
                'calculated' => false,
            ], [
                'name' => 'Salary/Wage Adjustment',
                'description' => 'Manual adjustments',
                'calculated' => false,
            ], [
                'name' => 'Sick Leave',
                'description' => 'Sick leave',
                'calculated' => false,
            ], [
                'name' => 'Overtime Pay',
                'description' => 'Overtime pay',
                'calculated' => false,
            ], [
                'name' => 'Substitution Pay',
                'description' => 'Substition pay',
                'calculated' => false,
            ],
        ];

        DB::table('additions')->insert($additions);

        $deductions = [
            [
                'name' => 'Income Tax',
                'description' => 'Income tax (computed)',
                'calculated' => true,
            ], [
                'name' => 'SSS Contribution',
                'description' => 'Mandatory SSS contribution (computed)',
                'calculated' => true,
            ], [
                'name' => 'PhilHealth Contribution',
                'description' => 'Mandatory PhilHealth contribution (computed)',
                'calculated' => true,
            ], [
                'name' => 'Pag-IBIG Contribution',
                'description' => 'Mandatory Pag-IBIG contribution (edit through account)',
                'calculated' => true,
            ], [
                'name' => 'Salary/Wage Adjustment',
                'description' => 'Manual adjustments',
                'calculated' => false,
            ], [
                'name' => 'Absences',
                'description' => 'Absenses',
                'calculated' => false,
            ], [
                'name' => 'PERAA',
                'description' => 'PERAA',
                'calculated' => false,
            ], [
                'name' => 'MP2',
                'description' => 'MP2',
                'calculated' => false,
            ], [
                'name' => 'SM/SLA',
                'description' => 'SM/SLA',
                'calculated' => false,
            ], [
                'name' => 'SM Card',
                'description' => 'SM card',
                'calculated' => false,
            ], [
                'name' => 'SSS Loan',
                'description' => 'SSS loan',
                'calculated' => false,
            ], [
                'name' => 'SSS Calamity Loan',
                'description' => 'SSS calamity loan',
                'calculated' => false,
            ], [
                'name' => 'PERAA Loan',
                'description' => 'PERAA loan',
                'calculated' => false,
            ], [
                'name' => 'HDMF Loan',
                'description' => 'HDMF loan',
                'calculated' => false,
            ], [
                'name' => 'AR Phone',
                'description' => 'AR phone',
                'calculated' => false,
            ], [
                'name' => 'AR Phone',
                'description' => 'AR phone',
                'calculated' => false,
            ], [
                'name' => 'HMO',
                'description' => 'HMO',
                'calculated' => false,
            ], [
                'name' => 'Special Exam',
                'description' => 'Special exam',
                'calculated' => false,
            ], [
                'name' => 'AR Others',
                'description' => 'AR others',
                'calculated' => false,
            ], [
                'name' => 'Grades Penalty',
                'description' => 'Grades penalty',
                'calculated' => false,
            ], [
                'name' => 'Bike Loan',
                'description' => 'Bike loan',
                'calculated' => false,
            ],
        ];

        DB::table('deductions')->insert($deductions);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deduction_items');
        Schema::dropIfExists('deductions');
        Schema::dropIfExists('addition_items');
        Schema::dropIfExists('additions');
        Schema::dropIfExists('payroll_items');
        Schema::dropIfExists('payroll_periods');
    }
};
