<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class PurchaseTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'total_spent' => fake()->randomFloat(2,10,150),
            'total_saving' => fake()->randomFloat(2,50,150),
            'transaction_at' => fake()->dateTimeBetween(startDate: "-40 days")
        ];
    }
}
