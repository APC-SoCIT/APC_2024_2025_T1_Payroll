<?php

namespace Database\Factories;

use App\Enums\RoleId;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'active' => true,
        ];
    }

    public function authorized(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'email' => config('roles.hr_accounts')[0],
            ];
        })->afterCreating(function (User $user) {
            UserRole::create([
                'user_id' => $user->id,
                'role_id' => RoleId::Payroll->value,
            ]);
            UserRole::create([
                'user_id' => $user->id,
                'role_id' => RoleId::Hr->value,
            ]);
        });
    }
}
