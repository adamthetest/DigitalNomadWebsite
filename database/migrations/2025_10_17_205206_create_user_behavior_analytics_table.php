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
        Schema::create('user_behavior_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('session_id')->nullable();
            $table->string('event_type'); // page_view, click, search, favorite, apply, etc.
            $table->string('entity_type')->nullable(); // city, job, article, etc.
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('event_data')->nullable(); // Additional event-specific data
            $table->json('user_context')->nullable(); // User state at time of event
            $table->string('page_url')->nullable();
            $table->string('referrer')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('ip_address')->nullable();
            $table->decimal('engagement_score', 5, 2)->default(0); // AI-calculated engagement score
            $table->timestamp('event_timestamp');
            $table->timestamps();

            $table->index(['user_id', 'event_timestamp']);
            $table->index(['event_type', 'event_timestamp']);
            $table->index(['entity_type', 'entity_id']);
            $table->index('session_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_behavior_analytics');
    }
};
