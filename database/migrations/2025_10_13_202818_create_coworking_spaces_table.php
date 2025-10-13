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
        Schema::create('coworking_spaces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained()->onDelete('cascade');
            $table->foreignId('neighborhood_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('website')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->enum('type', ['coworking', 'cafe', 'library', 'hotel_lobby', 'other']);
            $table->integer('wifi_speed_mbps')->nullable();
            $table->enum('wifi_reliability', ['poor', 'fair', 'good', 'excellent'])->nullable();
            $table->enum('noise_level', ['quiet', 'moderate', 'loud'])->nullable();
            $table->integer('seating_capacity')->nullable();
            $table->boolean('has_power_outlets')->default(false);
            $table->boolean('has_air_conditioning')->default(false);
            $table->boolean('has_kitchen')->default(false);
            $table->boolean('has_meeting_rooms')->default(false);
            $table->boolean('has_printing')->default(false);
            $table->boolean('is_24_hours')->default(false);
            $table->decimal('daily_rate', 8, 2)->nullable();
            $table->decimal('monthly_rate', 8, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->json('amenities')->nullable();
            $table->json('images')->nullable();
            $table->integer('rating')->nullable(); // 1-5 scale
            $table->text('notes')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['city_id', 'type', 'is_active']);
            $table->index(['neighborhood_id', 'is_active']);
            $table->index(['wifi_speed_mbps', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coworking_spaces');
    }
};
