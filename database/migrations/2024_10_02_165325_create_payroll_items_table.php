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
            $table->decimal('amount')->default(0);
            $table->timestamps();
        });

        Schema::create('additions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->boolean('required');
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
            $table->decimal('amount')->default(0);
            $table->timestamps();
        });

        Schema::create('deductions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->boolean('required');
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
            $table->decimal('amount')->default(0);
            $table->timestamps();
        });

        $additions = [
            [
                'name' => 'Salary/Wage',
                'description' => 'Base pay based on contract (edit through account)',
                'required' => true,
                'calculated' => true,
            ], [
                'name' => 'Deminimis Benefits',
                'description' => 'Deminimis Benefits',
                'required' => false,
                'calculated' => false,
            ], [
                'name' => 'Professional Fee',
                'description' => 'Professional fees, including consultation and adviser fees',
                'required' => false,
                'calculated' => false,
            ], [
                'name' => 'Allowance',
                'description' => 'Allowance',
                'required' => false,
                'calculated' => false,
            ], [
                'name' => 'Honorarium',
                'description' => 'Honorarium',
                'required' => false,
                'calculated' => false,
            ], [
                'name' => 'Merit Increase',
                'description' => 'Merit Increase',
                'required' => false,
                'calculated' => false,
            ], [
                'name' => 'Allowance Adjustment',
                'description' => 'Allowance Adjustment',
                'required' => false,
                'calculated' => false,
            ], [
                'name' => 'Honorarium Others',
                'description' => 'Honorarium others',
                'required' => false,
                'calculated' => false,
            ], [
                'name' => 'Professional Fee Others',
                'description' => 'Professional fee others',
                'required' => false,
                'calculated' => false,
            ], [
                'name' => 'Salary/Wage Adjustment',
                'description' => 'Manual adjustments',
                'required' => false,
                'calculated' => false,
            ], [
                'name' => 'Sick Leave',
                'description' => 'Sick leave',
                'required' => false,
                'calculated' => false,
            ], [
                'name' => 'Overtime Pay',
                'description' => 'Overtime pay',
                'required' => false,
                'calculated' => false,
            ], [
                'name' => 'Substitution Pay',
                'description' => 'Substition pay',
                'required' => false,
                'calculated' => false,
            ],
        ];

        DB::table('additions')->insert($additions);

        $deductions = [
            [
                'name' => 'Income Tax',
                'description' => 'Income tax (computed)',
                'required' => true,
                'calculated' => true,
            ], [
                'name' => 'SSS Contribution',
                'description' => 'Mandatory SSS contribution (computed)',
                'required' => false,
                'calculated' => true,
            ], [
                'name' => 'PhilHealth Contribution',
                'description' => 'Mandatory PhilHealth contribution (computed)',
                'required' => false,
                'calculated' => true,
            ], [
                'name' => 'Pag-IBIG Contribution',
                'description' => 'Mandatory Pag-IBIG contribution (edit through account)',
                'required' => false,
                'calculated' => true,
            ], [
                'name' => 'Salary/Wage Adjustment',
                'description' => 'Manual adjustments',
                'required' => false,
                'calculated' => false,
            ], [
                'name' => 'Absences',
                'description' => 'Absenses',
                'required' => false,
                'calculated' => false,
            ], [
                'name' => 'PERAA',
                'description' => 'PERAA',
                'required' => false,
                'calculated' => false,
            ], [
                'name' => 'MP2',
                'description' => 'MP2',
                'required' => false,
                'calculated' => false,
            ], [
                'name' => 'SM/SLA',
                'description' => 'SM/SLA',
                'required' => false,
                'calculated' => false,
            ], [
                'name' => 'SM Card',
                'description' => 'SM card',
                'required' => false,
                'calculated' => false,
            ], [
                'name' => 'SSS Loan',
                'description' => 'SSS loan',
                'required' => false,
                'calculated' => false,
            ], [
                'name' => 'SSS Calamity Loan',
                'description' => 'SSS calamity loan',
                'required' => false,
                'calculated' => false,
            ], [
                'name' => 'PERAA Loan',
                'description' => 'PERAA loan',
                'required' => false,
                'calculated' => false,
            ], [
                'name' => 'HDMF Loan',
                'description' => 'HDMF loan',
                'required' => false,
                'calculated' => false,
            ], [
                'name' => 'AR Phone',
                'description' => 'AR phone',
                'required' => false,
                'calculated' => false,
            ], [
                'name' => 'AR Phone',
                'description' => 'AR phone',
                'required' => false,
                'calculated' => false,
            ], [
                'name' => 'HMO',
                'description' => 'HMO',
                'required' => false,
                'calculated' => false,
            ], [
                'name' => 'Special Exam',
                'description' => 'Special exam',
                'required' => false,
                'calculated' => false,
            ], [
                'name' => 'AR Others',
                'description' => 'AR others',
                'required' => false,
                'calculated' => false,
            ], [
                'name' => 'Grades Penalty',
                'description' => 'Grades penalty',
                'required' => false,
                'calculated' => false,
            ], [
                'name' => 'Bike Loan',
                'description' => 'Bike loan',
                'required' => false,
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
