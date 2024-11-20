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
     * FIXME: Refactor using decimal maths (integers/BCMath/PHPMoney, etc.)
     */
    public function up(): void
    {
        Schema::create('cutoffs', function (Blueprint $table) {
            $table->id();
            $table->date('start_date');
            $table->date('cutoff_date');
            $table->date('end_date');
            $table->boolean('month_end');
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
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->boolean('required');
            $table->boolean('calculated');
            $table->boolean('taxable');
            $table->boolean('hour_based');
            $table->boolean('hr_access');
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
            $table->tinyInteger('hours')->default(0);
            $table->tinyInteger('minutes')->default(0);
            $table->timestamps();
        });

        Schema::create('deductions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->boolean('required');
            $table->boolean('calculated');
            $table->boolean('has_deadline');
            $table->boolean('taxable');
            $table->boolean('hour_based');
            $table->boolean('hr_access');
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
            $table->smallInteger('total_payments')->default(0);
            $table->smallInteger('remaining_payments')->default(0);
            $table->tinyInteger('hours')->default(0);
            $table->tinyInteger('minutes')->default(0);
            $table->timestamps();
        });

        /**
         * DO NOT drop rows, for record keeping purposes
         */
        $additions = [
            [
                'id' => AdditionId::Salary->value,
                'name' => 'Salary/Wage',
                'description' => 'Base pay based on contract',
                'required' => true,
                'calculated' => false,
                'taxable' => true,
                'hour_based' => false,
                'hr_access' => false,
            ], [
                'id' => AdditionId::Deminimis->value,
                'name' => 'Deminimis Benefits',
                'description' => 'Deminimis Benefits',
                'required' => false,
                'calculated' => false,
                'taxable' => false,
                'hour_based' => false,
                'hr_access' => false,
            ], [
                'id' => AdditionId::ProfessionalFee->value,
                'name' => 'Professional Fee',
                'description' => 'Professional fees, including consultation and adviser fees',
                'required' => false,
                'calculated' => false,
                'taxable' => false,
                'hour_based' => false,
                'hr_access' => false,
            ], [
                'id' => AdditionId::Allowance->value,
                'name' => 'Allowance',
                'description' => 'Allowance',
                'required' => false,
                'calculated' => false,
                'taxable' => false,
                'hour_based' => false,
                'hr_access' => false,
            ], [
                'id' => AdditionId::Honorarium->value,
                'name' => 'Honorarium',
                'description' => 'Honorarium',
                'required' => false,
                'calculated' => false,
                'taxable' => false,
                'hour_based' => false,
                'hr_access' => false,
            ], [
                'id' => AdditionId::Merit->value,
                'name' => 'Merit Increase',
                'description' => 'Merit Increase',
                'required' => false,
                'calculated' => false,
                'taxable' => false, // not used for the annualized projection
                'hour_based' => false,
                'hr_access' => false,
            ], [
                'id' => AdditionId::AllowanceAdjustment->value,
                'name' => 'Allowance Adjustment',
                'description' => 'Allowance Adjustment',
                'required' => false,
                'calculated' => false,
                'taxable' => false,
                'hour_based' => false,
                'hr_access' => false,
            ], [
                'id' => AdditionId::PreviousTaxable->value,
                'name' => 'Total Previous Taxable Income',
                'description' => 'Taxable compensation income from previous employer',
                'required' => false,
                'calculated' => false,
                'taxable' => false, // not needed to get this month's gross
                'hour_based' => false,
                'hr_access' => false,
            ], [
                'id' => AdditionId::HonorariumOthers->value,
                'name' => 'Honorarium Others',
                'description' => 'Honorarium others',
                'required' => false,
                'calculated' => false,
                'taxable' => true,
                'hour_based' => false,
                'hr_access' => false,
            ], [
                'id' => AdditionId::ProfessionalFeeOthers->value,
                'name' => 'Professional Fee Others',
                'description' => 'Professional fee others',
                'required' => false,
                'calculated' => false,
                'taxable' => false,
                'hour_based' => false,
                'hr_access' => false,
            ], [
                'id' => AdditionId::SalaryAdjustment->value,
                'name' => 'Salary/Wage Adjustment',
                'description' => 'Manual adjustments',
                'required' => false,
                'calculated' => false,
                'taxable' => false, // not used for the annualized projection
                'hour_based' => false,
                'hr_access' => false,
            ], [
                'id' => AdditionId::SickLeave->value,
                'name' => 'Sick Leave',
                'description' => 'Sick leave',
                'required' => false,
                'calculated' => false,
                'taxable' => true,
                'hour_based' => true,
                'hr_access' => false,
            ], [
                'id' => AdditionId::Overtime->value,
                'name' => 'Overtime Pay (Regular)',
                'description' => 'Overtime pay',
                'required' => false,
                'calculated' => false,
                'taxable' => true,
                'hour_based' => true,
                'hr_access' => true,
            ], [
                'id' => AdditionId::OvertimeNight->value,
                'name' => 'Overtime Pay (Night)',
                'description' => 'Overtime pay',
                'required' => false,
                'calculated' => false,
                'taxable' => true,
                'hour_based' => true,
                'hr_access' => true,
            ], [
                'id' => AdditionId::OvertimeRest->value,
                'name' => 'Overtime Pay (Rest Day/Special Holiday)',
                'description' => 'Overtime pay',
                'required' => false,
                'calculated' => false,
                'taxable' => true,
                'hour_based' => true,
                'hr_access' => true,
            ], [
                'id' => AdditionId::OvertimeRestExcess->value,
                'name' => 'Overtime Pay (Excess on Rest Day/Special Holiday)',
                'description' => 'Overtime pay',
                'required' => false,
                'calculated' => false,
                'taxable' => true,
                'hour_based' => true,
                'hr_access' => true,
            ], [
                'id' => AdditionId::OvertimeRestNight->value,
                'name' => 'Overtime Pay (Night on Rest Day/Special Holiday)',
                'description' => 'Overtime pay',
                'required' => false,
                'calculated' => false,
                'taxable' => true,
                'hour_based' => true,
                'hr_access' => true,
            ], [
                'id' => AdditionId::OvertimeHoliday->value,
                'name' => 'Overtime Pay (Regular Holiday)',
                'description' => 'Overtime pay',
                'required' => false,
                'calculated' => false,
                'taxable' => true,
                'hour_based' => true,
                'hr_access' => true,
            ], [
                'id' => AdditionId::OvertimeHolidayExcess->value,
                'name' => 'Overtime Pay (Excess on Regular Holiday)',
                'description' => 'Overtime pay',
                'required' => false,
                'calculated' => false,
                'taxable' => true,
                'hour_based' => true,
                'hr_access' => true,
            ], [
                'id' => AdditionId::OvertimeHolidayNight->value,
                'name' => 'Overtime Pay (Night on Regular Holiday)',
                'description' => 'Overtime pay',
                'required' => false,
                'calculated' => false,
                'taxable' => true,
                'hour_based' => true,
                'hr_access' => true,
            ], [
                'id' => AdditionId::SubstitutionPay->value,
                'name' => 'Substitution Pay (College)',
                'description' => 'Substitution pay (College)',
                'required' => false,
                'calculated' => false,
                'taxable' => true,
                'hour_based' => true,
                'hr_access' => false,
            ], [
                'id' => AdditionId::SubstitutionPayShs->value,
                'name' => 'Substitution Pay (SHS)',
                'description' => 'Substitution pay (SHS)',
                'required' => false,
                'calculated' => false,
                'taxable' => true,
                'hour_based' => true,
                'hr_access' => false,
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
                'has_deadline' => false,
                'taxable' => true, // not used for the annualized projection
                'hour_based' => false,
                'hr_access' => false,
            ], [
                'id' => DeductionId::Sss->value,
                'name' => 'SSS Contribution',
                'description' => 'Mandatory SSS contribution (computed)',
                'required' => true,
                'calculated' => true,
                'has_deadline' => false,
                'taxable' => false,
                'hour_based' => false,
                'hr_access' => false,
            ], [
                'id' => DeductionId::Philhealth->value,
                'name' => 'PhilHealth Contribution',
                'description' => 'Mandatory PhilHealth contribution (computed)',
                'required' => true,
                'calculated' => true,
                'has_deadline' => false,
                'taxable' => false,
                'hour_based' => false,
                'hr_access' => false,
            ], [
                'id' => DeductionId::Pagibig->value,
                'name' => 'Pag-IBIG Contribution',
                'description' => 'Mandatory Pag-IBIG contribution',
                'required' => true,
                'calculated' => false,
                'has_deadline' => false,
                'taxable' => false,
                'hour_based' => false,
                'hr_access' => false,
            ], [
                'id' => DeductionId::PreviousTaxWithheld->value,
                'name' => 'Total Previous Tax Withheld',
                'description' => 'Total taxes withheld from previous employer',
                'required' => false,
                'calculated' => false,
                'has_deadline' => false,
                'taxable' => true,
                'hour_based' => false,
                'hr_access' => false,
            ], [
                'id' => DeductionId::Absences->value,
                'name' => 'Absences',
                'description' => 'Absenses',
                'required' => false,
                'calculated' => false,
                'has_deadline' => false,
                'taxable' => true,
                'hour_based' => true,
                'hr_access' => false,
            ], [
                'id' => DeductionId::ClassAbsences->value,
                'name' => 'Class absences (College)',
                'description' => 'Class absenses (College)',
                'required' => false,
                'calculated' => false,
                'has_deadline' => false,
                'taxable' => true,
                'hour_based' => true,
                'hr_access' => false,
            ], [
                'id' => DeductionId::ClassAbsencesShs->value,
                'name' => 'Class absences (SHS)',
                'description' => 'Class absenses (SHS)',
                'required' => false,
                'calculated' => false,
                'has_deadline' => false,
                'taxable' => true,
                'hour_based' => true,
                'hr_access' => false,
            ], [
                'id' => DeductionId::Peraa->value,
                'name' => 'PERAA',
                'description' => 'PERAA',
                'required' => false,
                'calculated' => true,
                'has_deadline' => false,
                'taxable' => true,
                'hour_based' => false,
                'hr_access' => false,
            ], [
                'id' => DeductionId::Mp2->value,
                'name' => 'MP2',
                'description' => 'MP2',
                'required' => false,
                'calculated' => false,
                'has_deadline' => true,
                'taxable' => true,
                'hour_based' => false,
                'hr_access' => false,
            ], [
                'id' => DeductionId::Sla->value,
                'name' => 'SM/SLA',
                'description' => 'SM/SLA',
                'required' => false,
                'calculated' => false,
                'has_deadline' => false,
                'taxable' => true,
                'hour_based' => false,
                'hr_access' => false,
            ], [
                'id' => DeductionId::SmCard->value,
                'name' => 'SM Card',
                'description' => 'SM card',
                'required' => false,
                'calculated' => false,
                'has_deadline' => false,
                'taxable' => true,
                'hour_based' => false,
                'hr_access' => false,
            ], [
                'id' => DeductionId::SssLoan->value,
                'name' => 'SSS Loan',
                'description' => 'SSS loan',
                'required' => false,
                'calculated' => false,
                'has_deadline' => true,
                'taxable' => true,
                'hour_based' => false,
                'hr_access' => false,
            ], [
                'id' => DeductionId::SssCalamityLoan->value,
                'name' => 'SSS Calamity Loan',
                'description' => 'SSS calamity loan',
                'required' => false,
                'calculated' => false,
                'has_deadline' => true,
                'taxable' => true,
                'hour_based' => false,
                'hr_access' => false,
            ], [
                'id' => DeductionId::PeraaLoan->value,
                'name' => 'PERAA Loan',
                'description' => 'PERAA loan',
                'required' => false,
                'calculated' => false,
                'has_deadline' => true,
                'taxable' => true,
                'hour_based' => false,
                'hr_access' => false,
            ], [
                'id' => DeductionId::HdmfLoan->value,
                'name' => 'HDMF Loan',
                'description' => 'HDMF loan',
                'required' => false,
                'calculated' => false,
                'has_deadline' => true,
                'taxable' => true,
                'hour_based' => false,
                'hr_access' => false,
            ], [
                'id' => DeductionId::ArPhone->value,
                'name' => 'AR Phone',
                'description' => 'AR phone',
                'required' => false,
                'calculated' => false,
                'has_deadline' => false,
                'taxable' => true,
                'hour_based' => false,
                'hr_access' => false,
            ], [
                'id' => DeductionId::Hmo->value,
                'name' => 'HMO',
                'description' => 'HMO',
                'required' => false,
                'calculated' => false,
                'has_deadline' => false,
                'taxable' => true,
                'hour_based' => false,
                'hr_access' => false,
            ], [
                'id' => DeductionId::SpecialExam->value,
                'name' => 'Special Exam',
                'description' => 'Special exam',
                'required' => false,
                'calculated' => false,
                'has_deadline' => false,
                'taxable' => true,
                'hour_based' => false,
                'hr_access' => false,
            ], [
                'id' => DeductionId::ArOthers->value,
                'name' => 'AR Others',
                'description' => 'AR others',
                'required' => false,
                'calculated' => false,
                'has_deadline' => false,
                'taxable' => true,
                'hour_based' => false,
                'hr_access' => false,
            ], [
                'id' => DeductionId::GradesPenalty->value,
                'name' => 'Grades Penalty',
                'description' => 'Grades penalty',
                'required' => false,
                'calculated' => false,
                'has_deadline' => false,
                'taxable' => true,
                'hour_based' => false,
                'hr_access' => false,
            ], [
                'id' => DeductionId::BikeLoan->value,
                'name' => 'Bike Loan',
                'description' => 'Bike loan',
                'required' => false,
                'calculated' => false,
                'has_deadline' => false,
                'taxable' => true,
                'hour_based' => false,
                'hr_access' => false,
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
