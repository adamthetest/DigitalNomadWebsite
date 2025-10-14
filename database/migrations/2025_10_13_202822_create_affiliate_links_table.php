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
        Schema::create('affiliate_links', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('original_url');
            $table->string('affiliate_url');
            $table->string('affiliate_provider'); // booking.com, airbnb, etc.
            $table->enum('category', ['accommodation', 'transport', 'insurance', 'banking', 'vpn', 'gear', 'other']);
            $table->string('commission_type')->nullable(); // percentage, fixed, etc.
            $table->decimal('commission_rate', 5, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->json('tracking_params')->nullable(); // UTM parameters
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('click_count')->default(0);
            $table->integer('conversion_count')->default(0);
            $table->decimal('total_commission', 10, 2)->default(0);
            $table->timestamps();

            $table->index(['category', 'is_active']);
            $table->index(['affiliate_provider', 'is_active']);
            $table->index(['is_featured', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_links');
    }
};
