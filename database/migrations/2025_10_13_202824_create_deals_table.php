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
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_link_id')->nullable()->constrained()->onDelete('set null');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('deal_url');
            $table->string('provider'); // Company providing the deal
            $table->enum('category', ['accommodation', 'transport', 'insurance', 'banking', 'vpn', 'gear', 'other']);
            $table->decimal('original_price', 10, 2)->nullable();
            $table->decimal('discounted_price', 10, 2)->nullable();
            $table->decimal('discount_percentage', 5, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->string('promo_code')->nullable();
            $table->date('valid_from');
            $table->date('valid_until');
            $table->json('terms_conditions')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('click_count')->default(0);
            $table->integer('conversion_count')->default(0);
            $table->timestamps();

            $table->index(['category', 'is_active']);
            $table->index(['valid_from', 'valid_until', 'is_active']);
            $table->index(['is_featured', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deals');
    }
};
