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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('logo')->nullable();
            $table->string('website')->nullable();
            $table->text('remote_policy')->nullable();
            $table->string('industry')->nullable();
            $table->string('size')->nullable(); // e.g., "10-50", "50-200", "200+"
            $table->string('headquarters')->nullable();
            $table->boolean('verified')->default(false);
            $table->enum('subscription_plan', ['basic', 'premium', 'enterprise'])->default('basic');
            $table->json('benefits')->nullable(); // Array of company benefits
            $table->json('tech_stack')->nullable(); // Array of technologies used
            $table->string('contact_email')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['verified', 'is_active']);
            $table->index('subscription_plan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
