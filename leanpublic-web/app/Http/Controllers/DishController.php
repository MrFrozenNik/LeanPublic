<?php

namespace App\Http\Controllers;

use App\Models\Dish;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DishController extends Controller
{
    public function index(Request $request)
    {
        $dishes = $request->user()
            ->dishes()
            ->withCount('ingredients')
            ->with('ingredients')
            ->latest()
            ->paginate(15);

        return view('dishes.index', compact('dishes'));
    }

    public function create()
    {
        return view('dishes.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'servings' => 'required|integer|min:1|max:50',
        ]);

        $dish = $request->user()->dishes()->create($data);

        return redirect()->route('dishes.edit', $dish)
            ->with('success', 'Блюдо создано, теперь добавьте ингредиенты');
    }

    public function edit(Dish $dish)
    {
        Gate::authorize('update', $dish);
        $dish->load('ingredients');

        $availableIngredients = $dish->owner->ingredients()
            ->whereNotIn('id', $dish->ingredients->pluck('id'))
            ->orderBy('name')
            ->get();

        return view('dishes.edit', compact('dish', 'availableIngredients'));
    }

    public function update(Request $request, Dish $dish)
    {
        Gate::authorize('update', $dish);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'servings' => 'required|integer|min:1|max:50',
        ]);

        $dish->update($data);

        return redirect()->route('dishes.edit', $dish)
            ->with('success', 'Блюдо изменено');
    }

    public function destroy(Dish $dish)
    {
        Gate::authorize('delete', $dish);

        $dish->delete();

        return redirect()->route('dishes.index')
            ->with('success', 'Блюдо удалено');
    }
}
