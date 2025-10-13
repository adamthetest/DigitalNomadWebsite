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
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->text('description')->nullable();
            $table->text('overview')->nullable();
            $table->integer('population')->nullable();
            $table->string('climate')->nullable();
            $table->integer('internet_speed_mbps')->nullable();
            $table->integer('safety_score')->nullable(); // 1-10 scale
            $table->decimal('cost_of_living_index', 5, 2)->nullable();
            $table->string('best_time_to_visit')->nullable();
            $table->json('highlights')->nullable(); // Array of city highlights
            $table->json('images')->nullable(); // Array of image URLs
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['country_id', 'is_active']);
            $table->index(['is_featured', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
