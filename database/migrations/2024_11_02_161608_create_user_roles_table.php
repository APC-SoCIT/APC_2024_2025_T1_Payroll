<?php

use App\Enums\RoleId;
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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->timestamps();
        });

        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('role_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->timestamps();
        });

        $roles = [
            [
                'id' => RoleId::Admin->value,
                'name' => 'Admin',
                'description' => 'Manages account roles',
            ], [
                'id' => RoleId::Payroll->value,
                'name' => 'Payroll',
                'description' => 'Manages payroll entries',
            ], [
                'id' => RoleId::Hr->value,
                'name' => 'Human Resources',
                'description' => 'Manages account data',
            ]
        ];

        DB::table('roles')->insert($roles);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('roles');
    }
};
