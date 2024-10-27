<?php

use App\Enums\AdditionId;
use App\Enums\DeductionId;
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
        Schema::create('cutoffs', function (Blueprint $table) {
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
            $table->foreignId('cutoff_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->decimal('amount')->default(0);
            $table->timestamps();
        });

        Schema::create('additions', function (Blueprint $table) {
            $table->bigInteger('id')->unsigned();
            $table->primary('id');
            $table->string('name');
            $table->text('description');
            $table->boolean('required');
            $table->boolean('calculated');
            $table->timestamps();
        });

        Schema::create('item_additions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_item_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('addition_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->noActionOnDelete();
            $table->decimal('amount')->default(0);
            $table->timestamps();
        });

        Schema::create('deductions', function (Blueprint $table) {
            $table->bigInteger('id')->unsigned();
            $table->primary('id');
            $table->string('name');
            $table->text('description');
            $table->boolean('required');
            $table->boolean('calculated');
            $table->timestamps();
        });

        Schema::create('item_deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_item_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('deduction_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->noActionOnDelete();
            $table->decimal('amount')->default(0);
            $table->timestamps();
        });

        /**
         * DO NOT drop rows, for record keeping purposes
         */
        $additions = [
            [
                'id' => AdditionId::Salary->value,
                'name' => 'Salary/Wage',
                'description' => 'Base pay based on contract (edit through account)',
                'required' => true,
                'calculated' => true,
            ], [
                'id' => AdditionId::Deminimis->value,
                'name' => 'Deminimis Benefits',
                'description' => 'Deminimis Benefits',
                'required' => false,
                'calculated' => false,
            ], [
                'id' => AdditionId::ProfessionalFee->value,
                'name' => 'Professional Fee',
                'description' => 'Professional fees, including consultation and adviser fees',
                'required' => false,
                'calculated' => false,
            ], [
                'id' => AdditionId::Allowance->value,
                'name' => 'Allowance',
                'description' => 'Allowance',
                'required' => false,
                'calculated' => false,
            ], [
                'id' => AdditionId::Honorarium->value,
                'name' => 'Honorarium',
                'description' => 'Honorarium',
                'required' => false,
                'calculated' => false,
            ], [
                'id' => AdditionId::Merit->value,
                'name' => 'Merit Increase',
                'description' => 'Merit Increase',
                'required' => false,
                'calculated' => false,
            ], [
                'id' => AdditionId::AllowanceAdjustment->value,
                'name' => 'Allowance Adjustment',
                'description' => 'Allowance Adjustment',
                'required' => false,
                'calculated' => false,
            ], [
                'id' => AdditionId::HonorariumOthers->value,
                'name' => 'Honorarium Others',
                'description' => 'Honorarium others',
                'required' => false,
                'calculated' => false,
            ], [
                'id' => AdditionId::ProfessionalFeeOthers->value,
                'name' => 'Professional Fee Others',
                'description' => 'Professional fee others',
                'required' => false,
                'calculated' => false,
            ], [
                'id' => AdditionId::SalaryAdjustment->value,
                'name' => 'Salary/Wage Adjustment',
                'description' => 'Manual adjustments',
                'required' => false,
                'calculated' => false,
            ], [
                'id' => AdditionId::SickLeave->value,
                'name' => 'Sick Leave',
                'description' => 'Sick leave',
                'required' => false,
                'calculated' => false,
            ], [
                'id' => AdditionId::OvertimePay->value,
                'name' => 'Overtime Pay',
                'description' => 'Overtime pay',
                'required' => false,
                'calculated' => false,
            ], [
                'id' => AdditionId::SubstitutionPay->value,
                'name' => 'Substitution Pay',
                'description' => 'Substition pay',
                'required' => false,
                'calculated' => false,
            ],
        ];

        DB::table('additions')->insert($additions);

        /**
         * DO NOT drop rows, for record keeping purposes
         */
        $deductions = [
            [
                'id' => DeductionId::Tax->value,
                'name' => 'Income Tax',
                'description' => 'Income tax (computed)',
                'required' => true,
                'calculated' => true,
            ], [
                'id' => DeductionId::Sss->value,
                'name' => 'SSS Contribution',
                'description' => 'Mandatory SSS contribution (computed)',
                'required' => false,
                'calculated' => true,
            ], [
                'id' => DeductionId::Philhealth->value,
                'name' => 'PhilHealth Contribution',
                'description' => 'Mandatory PhilHealth contribution (computed)',
                'required' => false,
                'calculated' => true,
            ], [
                'id' => DeductionId::Pagibig->value,
                'name' => 'Pag-IBIG Contribution',
                'description' => 'Mandatory Pag-IBIG contribution (edit through account)',
                'required' => false,
                'calculated' => true,
            ], [
                'id' => DeductionId::SalaryAdjustment->value,
                'name' => 'Salary/Wage Adjustment',
                'description' => 'Manual adjustments',
                'required' => false,
                'calculated' => false,
            ], [
                'id' => DeductionId::Absences->value,
                'name' => 'Absences',
                'description' => 'Absenses',
                'required' => false,
                'calculated' => false,
            ], [
                'id' => DeductionId::Peraa->value,
                'name' => 'PERAA',
                'description' => 'PERAA',
                'required' => false,
                'calculated' => false,
            ], [
                'id' => DeductionId::Mp2->value,
                'name' => 'MP2',
                'description' => 'MP2',
                'required' => false,
                'calculated' => false,
            ], [
                'id' => DeductionId::Sla->value,
                'name' => 'SM/SLA',
                'description' => 'SM/SLA',
                'required' => false,
                'calculated' => false,
            ], [
                'id' => DeductionId::SmCard->value,
                'name' => 'SM Card',
                'description' => 'SM card',
                'required' => false,
                'calculated' => false,
            ], [
                'id' => DeductionId::SssLoan->value,
                'name' => 'SSS Loan',
                'description' => 'SSS loan',
                'required' => false,
                'calculated' => false,
            ], [
                'id' => DeductionId::SssCalamityLoan->value,
                'name' => 'SSS Calamity Loan',
                'description' => 'SSS calamity loan',
                'required' => false,
                'calculated' => false,
            ], [
                'id' => DeductionId::PeraaLoan->value,
                'name' => 'PERAA Loan',
                'description' => 'PERAA loan',
                'required' => false,
                'calculated' => false,
            ], [
                'id' => DeductionId::HdmfLoan->value,
                'name' => 'HDMF Loan',
                'description' => 'HDMF loan',
                'required' => false,
                'calculated' => false,
            ], [
                'id' => DeductionId::ArPhone->value,
                'name' => 'AR Phone',
                'description' => 'AR phone',
                'required' => false,
                'calculated' => false,
            ], [
                'id' => DeductionId::Hmo->value,
                'name' => 'HMO',
                'description' => 'HMO',
                'required' => false,
                'calculated' => false,
            ], [
                'id' => DeductionId::SpecialExam->value,
                'name' => 'Special Exam',
                'description' => 'Special exam',
                'required' => false,
                'calculated' => false,
            ], [
                'id' => DeductionId::ArOthers->value,
                'name' => 'AR Others',
                'description' => 'AR others',
                'required' => false,
                'calculated' => false,
            ], [
                'id' => DeductionId::GradesPenalty->value,
                'name' => 'Grades Penalty',
                'description' => 'Grades penalty',
                'required' => false,
                'calculated' => false,
            ], [
                'id' => DeductionId::BikeLoan->value,
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
        Schema::dropIfExists('item_deductions');
        Schema::dropIfExists('deductions');
        Schema::dropIfExists('item_additions');
        Schema::dropIfExists('additions');
        Schema::dropIfExists('payroll_items');
        Schema::dropIfExists('cutoffs');
    }
};
