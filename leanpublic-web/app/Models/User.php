<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function ingredients()
    {
        return $this->hasMany(Ingredient::class, 'owner_id');
    }

    public function dishes()
    {
        return $this->hasMany(Dish::class, 'owner_id');
    }

    public function diaryEntries()
    {
        return $this->hasMany(DiaryEntry::class);
    }

    public function clients()
    {
        return $this->belongsToMany(User::class, 'trainer_links', 'trainer_id', 'client_id')
            ->withTimestamps();
    }

    public function trainers()
    {
        return $this->belongsToMany(User::class, 'trainer_links', 'client_id', 'trainer_id')
            ->withTimestamps();
    }
}
