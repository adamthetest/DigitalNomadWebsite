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
        Schema::create('visa_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained()->onDelete('cascade');
            $table->string('nationality'); // Country code of the traveler's nationality
            $table->enum('visa_type', ['visa_free', 'visa_on_arrival', 'e_visa', 'visa_required', 'no_entry']);
            $table->integer('stay_duration_days')->nullable(); // How long they can stay
            $table->integer('validity_days')->nullable(); // How long the visa is valid
            $table->decimal('cost_usd', 8, 2)->nullable();
            $table->text('requirements')->nullable();
            $table->text('application_process')->nullable();
            $table->string('official_website')->nullable();
            $table->json('restrictions')->nullable(); // Any restrictions or special conditions
            $table->text('notes')->nullable();
            $table->date('last_updated')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['country_id', 'nationality', 'is_active']);
            $table->index(['visa_type', 'is_active']);
            $table->unique(['country_id', 'nationality']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visa_rules');
    }
};
