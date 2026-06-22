<?php

namespace App\Http\Controllers;

use App\Models\Dish;
use App\Models\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DishIngredientController extends Controller
{
    public function store(Request $request, Dish $dish)
    {
        Gate::authorize('update', $dish);

        $data = $request->validate([
            'ingredient_id' => 'required|exists:ingredients,id',
            'grams' => 'required|numeric|min:0.1|max:9999.99',
        ]);

        $ingredient = Ingredient::findOrFail($data['ingredient_id']);

        abort_unless($ingredient->owner_id === $dish->owner_id, 403);

        $dish->ingredients()->syncWithoutDetaching([
            $ingredient->id => ['grams' => $data['grams']],
        ]);

        return redirect()->route('dishes.edit', $dish)
            ->with('success', 'Ингредиент добавлен в блюдо');
    }

    public function destroy(Dish $dish, Ingredient $ingredient)
    {
        Gate::authorize('update', $dish);
        $dish->ingredients()->detach($ingredient->id);

        return redirect()->route('dishes.edit', $dish)
            ->with('success', 'Ингредиент удалён из блюда');
    }
}
