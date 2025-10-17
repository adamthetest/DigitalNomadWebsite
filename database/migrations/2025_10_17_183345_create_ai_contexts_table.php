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
        Schema::create('ai_contexts', function (Blueprint $table) {
            $table->id();
            $table->string('context_type'); // city, user, job, article, etc.
            $table->unsignedBigInteger('context_id'); // ID of the related model
            $table->string('context_model'); // Full model class name
            $table->json('context_data'); // Key data points for AI models
            $table->json('ai_embeddings')->nullable(); // Vector embeddings for similarity search
            $table->json('ai_summary')->nullable(); // AI-generated summary
            $table->json('ai_tags')->nullable(); // AI-generated tags
            $table->json('ai_insights')->nullable(); // AI-generated insights
            $table->string('ai_model_version')->nullable(); // Track which AI model was used
            $table->timestamp('last_ai_update')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['context_type', 'context_id']);
            $table->index(['context_model', 'context_id']);
            $table->index('last_ai_update');
            $table->index('ai_model_version');
            
            // Unique constraint to prevent duplicates
            $table->unique(['context_type', 'context_id', 'context_model']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_contexts');
    }
};