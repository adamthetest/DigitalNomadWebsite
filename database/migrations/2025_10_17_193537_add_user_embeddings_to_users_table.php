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
        Schema::table('users', function (Blueprint $table) {
            // Add AI-generated profile content (only if not exists)
            if (!Schema::hasColumn('users', 'ai_skills_analysis')) {
                $table->json('ai_skills_analysis')->nullable()->comment('AI analysis of user skills');
            }
            if (!Schema::hasColumn('users', 'ai_career_insights')) {
                $table->json('ai_career_insights')->nullable()->comment('AI-generated career insights');
            }
            if (!Schema::hasColumn('users', 'ai_resume_optimization_tips')) {
                $table->text('ai_resume_optimization_tips')->nullable()->comment('AI tips for resume optimization');
            }
            
            // Add matching metadata
            if (!Schema::hasColumn('users', 'matching_metadata')) {
                $table->json('matching_metadata')->nullable()->comment('Additional metadata for job matching');
            }
            if (!Schema::hasColumn('users', 'last_profile_update')) {
                $table->timestamp('last_profile_update')->nullable()->comment('When profile was last updated');
            }
            if (!Schema::hasColumn('users', 'last_embedding_update')) {
                $table->timestamp('last_embedding_update')->nullable()->comment('When embeddings were last updated');
            }
            
            // Add resume and cover letter fields
            if (!Schema::hasColumn('users', 'resume_content')) {
                $table->text('resume_content')->nullable()->comment('User resume content');
            }
            if (!Schema::hasColumn('users', 'resume_file_path')) {
                $table->string('resume_file_path')->nullable()->comment('Path to uploaded resume file');
            }
            if (!Schema::hasColumn('users', 'resume_metadata')) {
                $table->json('resume_metadata')->nullable()->comment('Resume metadata and parsing results');
            }
            if (!Schema::hasColumn('users', 'cover_letter_template')) {
                $table->text('cover_letter_template')->nullable()->comment('User cover letter template');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'profile_embedding',
                'skills_embedding',
                'experience_embedding',
                'job_matching_preferences',
                'preferred_job_types',
                'preferred_remote_types',
                'salary_expectations',
                'timezone_preferences',
                'ai_profile_summary',
                'ai_skills_analysis',
                'ai_career_insights',
                'ai_resume_optimization_tips',
                'matching_metadata',
                'last_profile_update',
                'last_embedding_update',
                'resume_content',
                'resume_file_path',
                'resume_metadata',
                'cover_letter_template'
            ]);
        });
    }
};