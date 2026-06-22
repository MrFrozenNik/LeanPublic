<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiaryEntry extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'dish_id', 'ingredient_id', 'grams', 'eaten_at'];

    protected function casts(): array
    {
        return [
            'eaten_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function dish()
    {
        return $this->belongsTo(Dish::class);
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function getTotalsAttribute(): array
    {
        $factor = $this->grams / 100;

        if ($this->ingredient) {
            return [
                'kcal' => $this->ingredient->kcal_100 * $factor,
                'protein' => $this->ingredient->protein_100 * $factor,
                'fat' => $this->ingredient->fat_100 * $factor,
                'carb' => $this->ingredient->carb_100 * $factor,
            ];
        }

        if ($this->dish) {
            $dishTotals = $this->dish->totals;
            $dishWeight = $this->dish->ingredients->sum('pivot.grams');

            if ($dishWeight <= 0) {
                return ['kcal' => 0, 'protein' => 0, 'fat' => 0, 'carb' => 0];
            }

            $portionFactor = $this->grams / $dishWeight;

            return [
                'kcal' => $dishTotals['kcal'] * $portionFactor,
                'protein' => $dishTotals['protein'] * $portionFactor,
                'fat' => $dishTotals['fat'] * $portionFactor,
                'carb' => $dishTotals['carb'] * $portionFactor,
            ];
        }

        return ['kcal' => 0, 'protein' => 0, 'fat' => 0, 'carb' => 0];
    }
}
