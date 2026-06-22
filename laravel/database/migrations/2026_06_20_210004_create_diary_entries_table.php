<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diary_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('dish_id')->nullable()->constrained('dishes')->onDelete('cascade');
            $table->foreignId('ingredient_id')->nullable()->constrained('ingredients')->onDelete('cascade');
            $table->decimal('grams', 8, 2);
            $table->timestamp('eaten_at');
            $table->timestamps();

            $table->index(['user_id', 'eaten_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diary_entries');
    }
};
