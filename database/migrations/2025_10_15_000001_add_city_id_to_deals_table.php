<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('deals') && ! Schema::hasColumn('deals', 'city_id')) {
            Schema::table('deals', function (Blueprint $table) {
                $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete()->after('id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('deals') && Schema::hasColumn('deals', 'city_id')) {
            Schema::table('deals', function (Blueprint $table) {
                $table->dropConstrainedForeignId('city_id');
            });
        }
    }
};



