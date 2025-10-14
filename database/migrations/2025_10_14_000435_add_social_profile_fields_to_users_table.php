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
            // Professional Details
            $table->string('tagline')->nullable()->after('bio');
            $table->string('job_title')->nullable()->after('tagline');
            $table->string('company')->nullable()->after('job_title');
            $table->json('skills')->nullable()->after('company');
            $table->enum('work_type', ['freelancer', 'employee', 'entrepreneur'])->nullable()->after('skills');
            $table->string('availability')->nullable()->after('work_type');
            
            // Nomad Lifestyle Info
            $table->string('location_current')->nullable()->after('location');
            $table->string('location_next')->nullable()->after('location_current');
            $table->json('travel_timeline')->nullable()->after('location_next');
            
            // Additional Social Links
            $table->string('behance')->nullable()->after('github');
            
            // Verification & Status
            $table->boolean('id_verified')->default(false)->after('is_public');
            $table->boolean('premium_status')->default(false)->after('id_verified');
            $table->timestamp('last_active')->nullable()->after('premium_status');
            
            // Privacy Controls
            $table->enum('visibility', ['public', 'members', 'hidden'])->default('public')->after('premium_status');
            $table->boolean('location_precise')->default(true)->after('visibility');
            $table->boolean('show_social_links')->default(true)->after('location_precise');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'tagline',
                'job_title',
                'company',
                'skills',
                'work_type',
                'availability',
                'location_current',
                'location_next',
                'travel_timeline',
                'behance',
                'id_verified',
                'premium_status',
                'last_active',
                'visibility',
                'location_precise',
                'show_social_links',
            ]);
        });
    }
};
