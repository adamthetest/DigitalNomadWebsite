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
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->text('requirements')->nullable();
            $table->text('benefits')->nullable();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['full-time', 'part-time', 'contract', 'freelance', 'internship'])->default('full-time');
            $table->enum('remote_type', ['fully-remote', 'hybrid', 'timezone-limited', 'onsite'])->default('fully-remote');
            $table->integer('salary_min')->nullable();
            $table->integer('salary_max')->nullable();
            $table->string('salary_currency', 3)->default('USD');
            $table->string('salary_period')->default('yearly'); // yearly, monthly, hourly
            $table->json('tags')->nullable(); // Array of skill tags
            $table->string('timezone')->nullable();
            $table->boolean('visa_support')->default(false);
            $table->enum('source', ['manual', 'scraped', 'api'])->default('manual');
            $table->string('source_url')->nullable();
            $table->string('apply_url');
            $table->string('apply_email')->nullable();
            $table->boolean('featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->integer('views_count')->default(0);
            $table->integer('applications_count')->default(0);
            $table->string('location')->nullable(); // For hybrid/onsite jobs
            $table->json('experience_level')->nullable(); // e.g., ["entry", "mid", "senior"]
            $table->timestamps();
            
            $table->index(['is_active', 'featured', 'published_at']);
            $table->index(['company_id', 'is_active']);
            $table->index(['type', 'remote_type']);
            $table->index('expires_at');
            $table->index('source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
