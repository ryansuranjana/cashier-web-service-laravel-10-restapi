<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => rand(1, 20),
            'payment_type_id' => rand(1, 20),
            'name' => fake()->word,
            'total_price' => fake()->randomFloat(2, 10, 100),
            'total_paid' => fake()->randomFloat(2, 5, 100),
            'total_return' => fake()->randomFloat(2, 0, 10),
            'receipt_code' => fake()->languageCode
        ];
    }
}
