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
        Schema::create('ai_generated_content', function (Blueprint $table) {
            $table->id();
            $table->string('content_type'); // blog_post, newsletter, summary, etc.
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('content');
            $table->text('excerpt')->nullable();
            $table->json('metadata')->nullable(); // AI prompts, sources, etc.
            $table->json('seo_data')->nullable(); // meta description, keywords, etc.
            $table->enum('status', ['draft', 'pending_review', 'approved', 'published', 'rejected'])->default('draft');
            $table->text('review_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('scheduled_at')->nullable(); // For scheduled publishing
            $table->timestamp('published_at')->nullable();
            $table->string('featured_image')->nullable();
            $table->json('tags')->nullable();
            $table->json('categories')->nullable();
            $table->integer('view_count')->default(0);
            $table->integer('engagement_score')->default(0); // Likes, shares, etc.
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['content_type', 'status']);
            $table->index(['status', 'scheduled_at']);
            $table->index(['is_featured', 'is_active']);
            $table->index(['published_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_generated_content');
    }
};
