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
        Schema::create('recommendation_engines', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('engine_type'); // collaborative, content_based, hybrid, etc.
            $table->string('target_entity'); // city, job, article, etc.
            $table->json('algorithm_config'); // Algorithm-specific configuration
            $table->json('feature_weights')->nullable(); // Feature importance weights
            $table->json('training_data')->nullable(); // Training data or model parameters
            $table->decimal('accuracy_score', 5, 2)->nullable(); // Model accuracy
            $table->integer('recommendation_count')->default(0); // Number of recommendations made
            $table->decimal('click_through_rate', 5, 2)->nullable(); // CTR for recommendations
            $table->decimal('conversion_rate', 5, 2)->nullable(); // Conversion rate
            $table->string('status')->default('training'); // training, active, inactive
            $table->timestamp('last_trained_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->index(['engine_type', 'status']);
            $table->index('target_entity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recommendation_engines');
    }
};