<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::all()->random()->id,
            'date' => $this->faker->date(),
            'group_id' => null,
            'category_id' => null,
            'amount' => $this->faker->numberBetween(1, 1000),
            'type' => $this->faker->randomElement(['income', 'expense', 'investment', 'savings']),
            'name' => $this->faker->word(),
        ];
    }
}
