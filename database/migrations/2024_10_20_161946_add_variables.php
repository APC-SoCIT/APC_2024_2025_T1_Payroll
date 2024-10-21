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
        Schema::dropIfExists('user_variable_items');
        Schema::dropIfExists('user_variables');
    }
};
