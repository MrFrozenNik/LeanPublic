<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ingredient;
use Illuminate\Support\Facades\Gate;

class IngredientController extends Controller
{

    public function index(Request $request)
    {
        $ingredients = $request->user()
            ->ingredients()
            ->latest()
            ->paginate(15);

        return view('ingredients.index', compact('ingredients'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'kcal_100' => 'required|numeric|min:0|max:9999.99',
            'protein_100' => 'required|numeric|min:0|max:9999.99',
            'fat_100' => 'required|numeric|min:0|max:9999.99',
            'carb_100' => 'required|numeric|min:0|max:9999.99',
        ]);

        $request->user()->ingredients()->create($data);

        return redirect()->route('ingredients.index')
            ->with('success', 'Ингредиент добавлен');
    }

    public function edit(Ingredient $ingredient)
    {
        Gate::authorize('update', $ingredient);

        return view('ingredients.edit', compact('ingredient'));
    }

    public function update(Request $request, Ingredient $ingredient)
    {
        Gate::authorize('update', $ingredient);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'kcal_100' => 'required|numeric|min:0|max:9999.99',
            'protein_100' => 'required|numeric|min:0|max:9999.99',
            'fat_100' => 'required|numeric|min:0|max:9999.99',
            'carb_100' => 'required|numeric|min:0|max:9999.99',
        ]);

        $ingredient->update($data);

        return redirect()->route('ingredients.index')
            ->with('success', 'Ингредиент изменён');
    }

    public function destroy(Ingredient $ingredient)
    {
        Gate::authorize('delete', $ingredient);

        $ingredient->delete();

        return redirect()->route('ingredients.index')
            ->with('success', 'Ингредиент удалён');
    }
}
