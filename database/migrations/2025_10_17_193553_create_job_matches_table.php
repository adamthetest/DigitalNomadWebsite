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
        Schema::create('job_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('job_id')->constrained()->onDelete('cascade');
            
            // Matching scores and factors
            $table->decimal('overall_score', 5, 2)->comment('Overall matching score (0-100)');
            $table->decimal('skills_score', 5, 2)->nullable()->comment('Skills matching score (0-100)');
            $table->decimal('experience_score', 5, 2)->nullable()->comment('Experience matching score (0-100)');
            $table->decimal('location_score', 5, 2)->nullable()->comment('Location compatibility score (0-100)');
            $table->decimal('salary_score', 5, 2)->nullable()->comment('Salary expectation score (0-100)');
            $table->decimal('culture_score', 5, 2)->nullable()->comment('Company culture fit score (0-100)');
            
            // Matching metadata
            $table->json('matching_factors')->nullable()->comment('Detailed matching factors');
            $table->json('ai_insights')->nullable()->comment('AI-generated matching insights');
            $table->text('match_reasoning')->nullable()->comment('Human-readable match reasoning');
            
            // User interaction tracking
            $table->boolean('user_viewed')->default(false)->comment('Whether user viewed this match');
            $table->boolean('user_applied')->default(false)->comment('Whether user applied to this job');
            $table->boolean('user_saved')->default(false)->comment('Whether user saved this match');
            $table->timestamp('viewed_at')->nullable()->comment('When user viewed this match');
            $table->timestamp('applied_at')->nullable()->comment('When user applied to this job');
            $table->timestamp('saved_at')->nullable()->comment('When user saved this match');
            
            // Recommendation metadata
            $table->string('recommendation_type')->default('algorithmic')->comment('Type of recommendation');
            $table->integer('recommendation_rank')->nullable()->comment('Rank in recommendation list');
            $table->json('recommendation_context')->nullable()->comment('Context for this recommendation');
            
            // AI-generated content
            $table->text('ai_application_tips')->nullable()->comment('AI tips for applying to this job');
            $table->text('ai_resume_suggestions')->nullable()->comment('AI suggestions for resume optimization');
            $table->text('ai_cover_letter_tips')->nullable()->comment('AI tips for cover letter');
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['user_id', 'overall_score']);
            $table->index(['job_id', 'overall_score']);
            $table->index(['user_id', 'recommendation_type']);
            $table->unique(['user_id', 'job_id']); // Prevent duplicate matches
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_matches');
    }
};