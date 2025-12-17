<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Plan;


class PlanFactory extends Factory
{
    // Link the factory to the Plan model
    protected $model = Plan::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
            'name' => $this->faker->unique()->word(), // random name
            'price' => $this->faker->numberBetween(1000, 50000), // random price
            'currency' => 'NGN', // default currency
            'description' => $this->faker->sentence(5), // optional description
            'is_active' => true, // active plan
        ];
    }
}
