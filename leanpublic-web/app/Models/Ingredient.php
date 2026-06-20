<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    use HasFactory;

    protected $fillable = ['owner_id', 'name', 'kcal_100', 'protein_100', 'fat_100', 'carb_100'];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function dishes()
    {
        return $this->belongsToMany(Dish::class, 'dish_ingredient')
            ->withPivot('grams')
            ->withTimestamps();
    }
}
