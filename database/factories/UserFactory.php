<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserVariableItem;
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

    public function configure(): Factory
    {
        return $this->afterCreating(function (User $user) {
            UserVariableItem::updateOrCreate([
                'user_id' => $user->id,
                'user_variable_id' => 1,
                'value' => 0,
            ]);
        });
    }

    public function authorized(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'email' => config('roles.hr_accounts')[0],
            ];
        });
    }
}
