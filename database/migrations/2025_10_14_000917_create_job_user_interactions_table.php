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
        Schema::create('job_user_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('job_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['saved', 'applied', 'rejected', 'shortlisted', 'interviewed', 'offered'])->default('saved');
            $table->text('notes')->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->timestamp('status_updated_at')->nullable();
            $table->json('application_data')->nullable(); // Store application form data
            $table->timestamps();

            $table->unique(['user_id', 'job_id']);
            $table->index(['user_id', 'status']);
            $table->index(['job_id', 'status']);
            $table->index('applied_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_user_interactions');
    }
};
