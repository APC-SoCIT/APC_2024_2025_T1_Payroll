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
        Schema::create('user_variables', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->timestamps();
        });

        Schema::create('user_variable_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('user_variable_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->decimal('value');
            $table->timestamps();
        });

        Schema::create('addition_variables', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->timestamps();
        });

        Schema::create('addition_variable_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('addition_item_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('addition_variable_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->decimal('value');
            $table->timestamps();
        });

        DB::table('addition_variables')->insert([
            'name' => 'Hourly rate',
            'description' => 'Base pay based on contract',
        ]);

        DB::table('addition_variables')->insert([
            'name' => 'Regular hours rendered',
            'description' => 'Number of hours rendered, not including overtime',
        ]);

        DB::table('addition_variables')->insert([
            'name' => 'Overtime hours rendered',
            'description' => 'Number of overtime hours rendered',
        ]);

        DB::table('user_variables')->insert([
            'name' => 'Hourly rate',
            'description' => 'Base pay based on contract',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addition_variable_items');
        Schema::dropIfExists('addition_variables');
        Schema::dropIfExists('user_variable_items');
        Schema::dropIfExists('user_variables');
    }
};
