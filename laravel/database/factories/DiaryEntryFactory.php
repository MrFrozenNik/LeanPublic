<?php

namespace Database\Factories;

use App\Models\DiaryEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DiaryEntry>
 */
class DiaryEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'dish_id' => null,
            'ingredient_id' => null,
            'grams' => fake()->randomFloat(2, 50, 400),
            'eaten_at' => fake()->dateTimeBetween('-14 days', 'now'),
        ];
    }
}
