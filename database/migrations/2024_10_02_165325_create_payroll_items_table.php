<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
            $table->decimal('amount', 8, 2);
            $table->timestamps();
        });

        Schema::create('deductions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
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
            $table->decimal('amount', 8, 2);
            $table->timestamps();
        });
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
