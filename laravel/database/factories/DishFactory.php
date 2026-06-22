<?php

namespace Database\Factories;

use App\Models\Dish;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Dish>
 */
class DishFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'owner_id' => User::factory(),
            'name' => fake()->randomElement([
                'Куриная грудка с рисом', 'Овсянка с бананом',
                'Творожный завтрак', 'Салат с лососем',
                'Гречка с овощами', 'Омлет с авокадо',
            ]),
            'servings' => fake()->numberBetween(1, 4),
        ];
    }
}
