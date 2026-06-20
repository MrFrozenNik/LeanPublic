<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dish_ingredient', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dish_id')->constrained('dishes')->onDelete('cascade');
            $table->foreignId('ingredient_id')->constrained('ingredients')->onDelete('cascade');
            $table->decimal('grams', 8, 2);
            $table->timestamps();

            $table->unique(['dish_id', 'ingredient_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dish_ingredient');
    }
};
