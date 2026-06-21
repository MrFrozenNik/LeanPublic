<?php

namespace Database\Seeders;

use App\Models\Dish;
use App\Models\Ingredient;
use App\Models\TrainerLink;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $trainer = User::factory()->create([
            'name' => 'Анна Тренер',
            'email' => 'trainer@example.com',
        ]);

        $client1 = User::factory()->create([
            'name' => 'Иван Клиент',
            'email' => 'client1@example.com',
        ]);

        $client2 = User::factory()->create([
            'name' => 'Мария Клиент',
            'email' => 'client2@example.com',
        ]);

        $soloUser = User::factory()->create([
            'name' => 'Соло Пользователь',
            'email' => 'solo@example.com',
        ]);

        TrainerLink::create(['trainer_id' => $trainer->id, 'client_id' => $client1->id]);
        TrainerLink::create(['trainer_id' => $trainer->id, 'client_id' => $client2->id]);

        foreach ([$client1, $client2, $soloUser] as $user) {
            $ingredients = Ingredient::factory()
                ->count(8)
                ->create(['owner_id' => $user->id]);

            $dishes = Dish::factory()
                ->count(3)
                ->create(['owner_id' => $user->id]);

            foreach ($dishes as $dish) {
                $selected = $ingredients->random(min(4, $ingredients->count()));

                foreach ($selected as $ingredient) {
                    $dish->ingredients()->attach($ingredient->id, [
                        'grams' => fake()->randomFloat(2, 20, 250),
                    ]);
                }
            }

            for ($i = 0; $i < 10; $i++) {
                $useDish = fake()->boolean(60);

                $user->diaryEntries()->create([
                    'dish_id' => $useDish ? $dishes->random()->id : null,
                    'ingredient_id' => $useDish ? null : $ingredients->random()->id,
                    'grams' => fake()->randomFloat(2, 50, 350),
                    'eaten_at' => fake()->dateTimeBetween('-7 days', 'now'),
                ]);
            }
        }
    }
}
