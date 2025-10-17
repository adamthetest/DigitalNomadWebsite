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
            // Enhanced professional profile
            $table->json('profession_details')->nullable()->after('job_title'); // Industry, seniority level, etc.
            $table->json('technical_skills')->nullable()->after('skills'); // Programming languages, tools
            $table->json('soft_skills')->nullable()->after('technical_skills'); // Communication, leadership, etc.
            $table->integer('experience_years')->nullable()->after('soft_skills');
            $table->string('education_level')->nullable()->after('experience_years'); // Bachelor's, Master's, etc.
            $table->json('certifications')->nullable()->after('education_level');

            // Travel and location preferences
            $table->json('preferred_climates')->nullable()->after('travel_timeline'); // tropical, temperate, etc.
            $table->json('preferred_activities')->nullable()->after('preferred_climates'); // hiking, beaches, culture
            $table->integer('budget_monthly_min')->nullable()->after('preferred_activities');
            $table->integer('budget_monthly_max')->nullable()->after('budget_monthly_min');
            $table->string('budget_currency', 3)->default('USD')->after('budget_monthly_max');
            $table->boolean('visa_flexible')->default(false)->after('budget_currency'); // Can work with visa restrictions

            // Work preferences
            $table->json('preferred_work_schedule')->nullable()->after('visa_flexible'); // Time zones, hours
            $table->json('work_environment_preferences')->nullable()->after('preferred_work_schedule'); // Quiet, social, etc.
            $table->boolean('requires_stable_internet')->default(true)->after('work_environment_preferences');
            $table->integer('min_internet_speed_mbps')->nullable()->after('requires_stable_internet');

            // Lifestyle preferences
            $table->json('lifestyle_tags')->nullable()->after('min_internet_speed_mbps'); // Nightlife, family-friendly, etc.
            $table->boolean('pet_friendly_needed')->default(false)->after('lifestyle_tags');
            $table->boolean('family_friendly_needed')->default(false)->after('pet_friendly_needed');
            $table->json('dietary_restrictions')->nullable()->after('family_friendly_needed');

            // AI-specific fields
            $table->json('ai_profile_summary')->nullable()->after('dietary_restrictions'); // AI-generated profile summary
            $table->json('ai_preferences_vector')->nullable()->after('ai_profile_summary'); // For matching algorithms
            $table->timestamp('ai_profile_updated_at')->nullable()->after('ai_preferences_vector');

            // Privacy and data preferences
            $table->boolean('ai_data_collection_consent')->default(false)->after('ai_profile_updated_at');
            $table->boolean('personalized_recommendations')->default(true)->after('ai_data_collection_consent');
            $table->json('data_sharing_preferences')->nullable()->after('personalized_recommendations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'profession_details',
                'technical_skills',
                'soft_skills',
                'experience_years',
                'education_level',
                'certifications',
                'preferred_climates',
                'preferred_activities',
                'budget_monthly_min',
                'budget_monthly_max',
                'budget_currency',
                'visa_flexible',
                'preferred_work_schedule',
                'work_environment_preferences',
                'requires_stable_internet',
                'min_internet_speed_mbps',
                'lifestyle_tags',
                'pet_friendly_needed',
                'family_friendly_needed',
                'dietary_restrictions',
                'ai_profile_summary',
                'ai_preferences_vector',
                'ai_profile_updated_at',
                'ai_data_collection_consent',
                'personalized_recommendations',
                'data_sharing_preferences',
            ]);
        });
    }
};
