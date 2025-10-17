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
        Schema::create('daily_metrics', function (Blueprint $table) {
            $table->id();
            $table->date('date')->index();
            $table->string('metric_type'); // 'city_cost', 'traffic', 'weather', 'user_activity', 'job_postings'
            $table->string('entity_type')->nullable(); // 'city', 'global', 'user', 'job'
            $table->unsignedBigInteger('entity_id')->nullable(); // ID of city, user, etc.
            $table->json('metrics'); // Flexible JSON structure for different metric types
            $table->json('metadata')->nullable(); // Additional context data
            $table->timestamps();

            // Indexes for efficient querying
            $table->index(['date', 'metric_type']);
            $table->index(['date', 'entity_type', 'entity_id']);
            $table->index(['metric_type', 'entity_type']);

            // Unique constraint to prevent duplicate metrics for same day/type/entity
            $table->unique(['date', 'metric_type', 'entity_type', 'entity_id'], 'unique_daily_metric');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_metrics');
    }
};
