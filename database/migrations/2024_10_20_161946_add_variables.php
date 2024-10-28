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
        Schema::create('variables', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->boolean('required')->default(false);
            $table->decimal('min')->default(0);
            $table->timestamps();
        });

        Schema::create('user_variables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('variable_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->decimal('value');
            $table->timestamps();
        });

        $userVariables = [
            [
                'name' => 'Salary/Wage',
                'description' => 'Base pay based on contract',
                'required' => true,
                'min' => 0,
            ], [
                'name' => 'Pag-IBIG Contribution',
                'description' => 'Mandatory Pag-IBIG contribution',
                'required' => true,
                'min' => 200,
            ]
        ];

        DB::table('variables')->insert($userVariables);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_variables');
        Schema::dropIfExists('variables');
    }
};
