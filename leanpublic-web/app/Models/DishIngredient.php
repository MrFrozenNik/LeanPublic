<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class DishIngredient extends Pivot
{
    protected $table = 'dish_ingredient';

    protected $fillable = ['dish_id', 'ingredient_id', 'grams'];
}
