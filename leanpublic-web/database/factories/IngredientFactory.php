<?php

namespace Database\Factories;

use App\Models\Ingredient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ingredient>
 */
class IngredientFactory extends Factory
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
                    'Куриная грудка', 'Рис белый', 'Гречка', 'Овсянка',
                    'Яйцо куриное', 'Творог 5%', 'Брокколи', 'Авокадо',
                    'Лосось', 'Картофель', 'Банан', 'Миндаль',
                    'Оливковое масло', 'Греческий йогурт', 'Тофу',
                ]) . ' #' . fake()->unique()->numberBetween(1, 9999),
            'kcal_100' => fake()->randomFloat(2, 30, 700),
            'protein_100' => fake()->randomFloat(2, 0, 35),
            'fat_100' => fake()->randomFloat(2, 0, 60),
            'carb_100' => fake()->randomFloat(2, 0, 80),
        ];
    }
}
