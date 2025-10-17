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
        Schema::create('predictions', function (Blueprint $table) {
            $table->id();
            $table->string('prediction_type'); // 'cost_trend', 'trending_city', 'user_growth', 'engagement'
            $table->string('entity_type')->nullable(); // 'city', 'global', 'user'
            $table->unsignedBigInteger('entity_id')->nullable(); // ID of city, user, etc.
            $table->date('prediction_date'); // Date for which prediction is made
            $table->json('prediction_data'); // The actual prediction values
            $table->json('confidence_scores')->nullable(); // Confidence levels for predictions
            $table->json('factors')->nullable(); // Factors that influenced the prediction
            $table->string('model_version')->default('1.0'); // Version of prediction model used
            $table->timestamp('generated_at');
            $table->timestamps();

            // Indexes for efficient querying
            $table->index(['prediction_type', 'prediction_date']);
            $table->index(['entity_type', 'entity_id']);
            $table->index(['prediction_date', 'prediction_type']);
            $table->index('generated_at');

            // Unique constraint to prevent duplicate predictions
            $table->unique(['prediction_type', 'entity_type', 'entity_id', 'prediction_date'], 'unique_prediction');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('predictions');
    }
};
