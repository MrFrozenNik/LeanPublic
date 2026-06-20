<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dish extends Model
{
    use HasFactory;

    protected $fillable = ['owner_id', 'name', 'servings'];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class, 'dish_ingredient')
            ->withPivot('grams')
            ->withTimestamps();
    }


    public function getTotalsAttribute(): array
    {
        $totals = ['kcal' => 0, 'protein' => 0, 'fat' => 0, 'carb' => 0];

        foreach ($this->ingredients as $ingredient) {
            $factor = $ingredient->pivot->grams / 100;
            $totals['kcal'] += $ingredient->kcal_100 * $factor;
            $totals['protein'] += $ingredient->protein_100 * $factor;
            $totals['fat'] += $ingredient->fat_100 * $factor;
            $totals['carb'] += $ingredient->carb_100 * $factor;
        }

        return $totals;
    }
}
