<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->morphs('favoritable'); // favoritable_id, favoritable_type
            $table->string('category')->nullable(); // 'city', 'article', 'deal', etc.
            $table->json('notes')->nullable(); // User's personal notes
            $table->timestamps();

            // Ensure a user can't favorite the same item twice
            $table->unique(['user_id', 'favoritable_id', 'favoritable_type']);

            // Indexes for performance
            $table->index(['user_id', 'category']);
            $table->index(['favoritable_id', 'favoritable_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};
