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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('city_id')->nullable()->constrained()->onDelete('set null');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->string('featured_image')->nullable();
            $table->json('images')->nullable();
            $table->enum('type', ['guide', 'news', 'review', 'comparison', 'tips']);
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->json('tags')->nullable();
            $table->json('meta_data')->nullable(); // SEO meta data
            $table->integer('view_count')->default(0);
            $table->integer('like_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_pinned')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'published_at']);
            $table->index(['city_id', 'status']);
            $table->index(['type', 'status']);
            $table->index(['is_featured', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
