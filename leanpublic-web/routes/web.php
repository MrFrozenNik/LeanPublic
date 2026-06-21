<?php

use App\Http\Controllers\Auth\GitHubController;
use App\Http\Controllers\DishController;
use App\Http\Controllers\DishIngredientController;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DiaryEntryController;
use App\Http\Controllers\TrainerDashboardController;
use App\Http\Controllers\TrainerLinkController;
use Illuminate\Support\Facades\Route;

Route::get('/auth/github', [GitHubController::class, 'redirect'])->name('auth.github');
Route::get('/auth/github/callback', [GitHubController::class, 'callback']);

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('ingredients', IngredientController::class)
        ->except(['show', 'create']);
    Route::resource('dishes', DishController::class)->except(['show']);

    Route::post('/dishes/{dish}/ingredients', [DishIngredientController::class, 'store'])
        ->name('dishes.ingredients.store');

    Route::delete('/dishes/{dish}/ingredients/{ingredient}', [DishIngredientController::class, 'destroy'])
        ->name('dishes.ingredients.destroy');

    Route::get('/diary', [DiaryEntryController::class, 'index'])->name('diary.index');
    Route::post('/diary', [DiaryEntryController::class, 'store'])->name('diary.store');
    Route::delete('/diary/{diaryEntry}', [DiaryEntryController::class, 'destroy'])->name('diary.destroy');



    Route::post('/trainers', [TrainerLinkController::class, 'store'])->name('trainers.store');
    Route::delete('/trainers/{trainerLink}', [TrainerLinkController::class, 'destroy'])->name('trainers.destroy');

    Route::get('/trainer/dashboard', [TrainerDashboardController::class, 'index'])->name('trainer.dashboard');
});

require __DIR__.'/auth.php';
