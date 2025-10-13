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
        Schema::create('security_logs', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address');
            $table->string('user_agent')->nullable();
            $table->string('event_type'); // login_attempt, failed_login, banned_access, etc.
            $table->string('severity')->default('info'); // info, warning, error, critical
            $table->text('message');
            $table->json('metadata')->nullable(); // Additional data
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('url')->nullable();
            $table->string('method')->nullable();
            $table->timestamps();
            
            $table->index(['ip_address', 'created_at']);
            $table->index(['event_type', 'created_at']);
            $table->index(['severity', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_logs');
    }
};
