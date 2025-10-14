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
        Schema::create('cost_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained()->onDelete('cascade');
            $table->string('category'); // accommodation, food, transport, entertainment, etc.
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price_min', 10, 2)->nullable();
            $table->decimal('price_max', 10, 2)->nullable();
            $table->decimal('price_average', 10, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->string('unit')->nullable(); // per night, per meal, per month, etc.
            $table->enum('price_range', ['budget', 'mid_range', 'luxury'])->nullable();
            $table->json('details')->nullable(); // Additional pricing details
            $table->text('notes')->nullable();
            $table->date('last_updated')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['city_id', 'category', 'is_active']);
            $table->index(['category', 'price_range']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cost_items');
    }
};
