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
        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('country_code', 2)->nullable();
            $table->json('interests')->nullable(); // Array of interests
            $table->enum('status', ['active', 'unsubscribed', 'bounced'])->default('active');
            $table->string('source')->nullable(); // How they subscribed
            $table->json('utm_data')->nullable(); // UTM tracking data
            $table->timestamp('last_email_sent')->nullable();
            $table->timestamp('subscribed_at');
            $table->timestamp('unsubscribed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'subscribed_at']);
            $table->index(['country_code', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newsletter_subscribers');
    }
};
