<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Voucher>
 */
class VoucherFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'code' => fake()->regexify('[A-Z]{3}[0-9]{2}[A-Z]{3}[0-9]{2}[A-Z]{3}[0-9]{2}[A-Z]{3}[0-9]{2}'), // random 20 chars with 3 strings and 2 numbers in every 5 chars
        ];
    }
}
