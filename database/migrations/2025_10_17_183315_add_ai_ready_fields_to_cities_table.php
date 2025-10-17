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
        Schema::table('cities', function (Blueprint $table) {
            // Enhanced cost data
            $table->decimal('cost_accommodation_monthly', 8, 2)->nullable()->after('cost_of_living_index');
            $table->decimal('cost_food_monthly', 8, 2)->nullable()->after('cost_accommodation_monthly');
            $table->decimal('cost_transport_monthly', 8, 2)->nullable()->after('cost_food_monthly');
            $table->decimal('cost_coworking_monthly', 8, 2)->nullable()->after('cost_transport_monthly');
            $table->string('cost_currency', 3)->default('USD')->after('cost_coworking_monthly');
            
            // Enhanced internet and connectivity
            $table->integer('internet_reliability_score')->nullable()->after('internet_speed_mbps'); // 1-10 scale
            $table->boolean('fiber_available')->default(false)->after('internet_reliability_score');
            $table->boolean('mobile_data_good')->default(false)->after('fiber_available');
            
            // Weather and climate data
            $table->json('weather_data')->nullable()->after('climate'); // Monthly averages, seasons
            $table->integer('avg_temperature_celsius')->nullable()->after('weather_data');
            $table->integer('avg_humidity_percent')->nullable()->after('avg_temperature_celsius');
            $table->integer('rainy_days_per_year')->nullable()->after('avg_humidity_percent');
            
            // Enhanced safety data
            $table->json('safety_details')->nullable()->after('safety_score'); // Crime rates, areas to avoid
            $table->boolean('female_safe')->default(false)->after('safety_details');
            $table->boolean('lgbtq_friendly')->default(false)->after('female_safe');
            
            // Visa and immigration data
            $table->json('visa_options')->nullable()->after('best_time_to_visit'); // Tourist, digital nomad, work visas
            $table->integer('visa_duration_days')->nullable()->after('visa_options');
            $table->boolean('visa_extensions_possible')->default(false)->after('visa_duration_days');
            $table->decimal('visa_cost_usd', 8, 2)->nullable()->after('visa_extensions_possible');
            
            // Nomad-specific amenities
            $table->integer('coworking_spaces_count')->nullable()->after('visa_cost_usd');
            $table->integer('cafes_with_wifi_count')->nullable()->after('coworking_spaces_count');
            $table->boolean('english_widely_spoken')->default(false)->after('cafes_with_wifi_count');
            $table->json('nomad_communities')->nullable()->after('english_widely_spoken'); // Facebook groups, meetups
            
            // AI-specific fields for context
            $table->json('ai_summary')->nullable()->after('nomad_communities'); // AI-generated city summary
            $table->json('ai_tags')->nullable()->after('ai_summary'); // AI-generated tags for matching
            $table->timestamp('ai_data_updated_at')->nullable()->after('ai_tags');
            
            // Data source tracking
            $table->string('data_source')->default('manual')->after('ai_data_updated_at');
            $table->timestamp('last_data_sync')->nullable()->after('data_source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropColumn([
                'cost_accommodation_monthly',
                'cost_food_monthly', 
                'cost_transport_monthly',
                'cost_coworking_monthly',
                'cost_currency',
                'internet_reliability_score',
                'fiber_available',
                'mobile_data_good',
                'weather_data',
                'avg_temperature_celsius',
                'avg_humidity_percent',
                'rainy_days_per_year',
                'safety_details',
                'female_safe',
                'lgbtq_friendly',
                'visa_options',
                'visa_duration_days',
                'visa_extensions_possible',
                'visa_cost_usd',
                'coworking_spaces_count',
                'cafes_with_wifi_count',
                'english_widely_spoken',
                'nomad_communities',
                'ai_summary',
                'ai_tags',
                'ai_data_updated_at',
                'data_source',
                'last_data_sync'
            ]);
        });
    }
};