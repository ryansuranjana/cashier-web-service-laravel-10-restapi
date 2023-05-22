<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sku' => fake()->unique()->word,
            'name' => fake()->word,
            'stock' => rand(1, 50),
            'price' => fake()->randomFloat(2, 0, 10),
            'image' => 'https://image',
            'category_id' => rand(1, 20)
        ];
    }
}
