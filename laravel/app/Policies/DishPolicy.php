<?php

namespace App\Policies;

use App\Models\Dish;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DishPolicy
{

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Dish $dish): bool
    {
        return $user->id === $dish->owner_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Dish $dish): bool
    {
        return $user->id === $dish->owner_id;
    }

}
