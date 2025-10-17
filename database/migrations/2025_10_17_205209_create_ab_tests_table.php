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
        Schema::create('ab_tests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('test_type'); // content, layout, feature, etc.
            $table->string('target_element'); // page, component, feature
            $table->json('variants'); // Array of test variants
            $table->json('traffic_allocation')->nullable(); // Traffic split percentages
            $table->string('status')->default('draft'); // draft, active, paused, completed
            $table->json('success_metrics'); // Metrics to track for success
            $table->json('targeting_rules')->nullable(); // Who should see this test
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->json('results')->nullable(); // Test results and statistics
            $table->string('winner_variant')->nullable(); // Winning variant
            $table->decimal('confidence_level', 5, 2)->nullable(); // Statistical confidence
            $table->timestamps();

            $table->index(['status', 'start_date']);
            $table->index('test_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ab_tests');
    }
};