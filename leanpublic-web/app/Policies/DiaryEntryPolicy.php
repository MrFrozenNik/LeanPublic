<?php

namespace App\Policies;

use App\Models\DiaryEntry;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DiaryEntryPolicy
{
    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DiaryEntry $diaryEntry): bool
    {
        return $user->id === $diaryEntry->user_id;
    }
}
