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
        Schema::create('neighborhoods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('type')->nullable(); // residential, business, tourist, etc.
            $table->integer('cost_level')->nullable(); // 1-5 scale
            $table->integer('safety_score')->nullable(); // 1-10 scale
            $table->integer('internet_speed_mbps')->nullable();
            $table->json('amenities')->nullable(); // Array of amenities
            $table->json('transportation')->nullable(); // Public transport info
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['city_id', 'is_active']);
            $table->unique(['city_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('neighborhoods');
    }
};
