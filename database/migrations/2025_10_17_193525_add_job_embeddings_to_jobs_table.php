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
        Schema::table('jobs', function (Blueprint $table) {
            // Add vector storage for job embeddings
            $table->json('job_embedding')->nullable()->comment('Vector embedding for job content');
            $table->json('skills_embedding')->nullable()->comment('Vector embedding for required skills');
            $table->json('company_embedding')->nullable()->comment('Vector embedding for company description');

            // Add job matching metadata
            $table->json('matching_metadata')->nullable()->comment('Additional metadata for job matching');
            $table->timestamp('last_embedding_update')->nullable()->comment('When embeddings were last updated');

            // Add AI-generated content fields
            $table->text('ai_job_summary')->nullable()->comment('AI-generated job summary');
            $table->json('ai_skills_extracted')->nullable()->comment('AI-extracted skills from job description');
            $table->json('ai_requirements_parsed')->nullable()->comment('AI-parsed job requirements');
            $table->text('ai_company_culture')->nullable()->comment('AI-generated company culture insights');

            // Add matching scores
            $table->decimal('match_score_base', 5, 2)->nullable()->comment('Base matching score (0-100)');
            $table->json('match_factors')->nullable()->comment('Factors contributing to match score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn([
                'job_embedding',
                'skills_embedding',
                'company_embedding',
                'matching_metadata',
                'last_embedding_update',
                'ai_job_summary',
                'ai_skills_extracted',
                'ai_requirements_parsed',
                'ai_company_culture',
                'match_score_base',
                'match_factors',
            ]);
        });
    }
};
