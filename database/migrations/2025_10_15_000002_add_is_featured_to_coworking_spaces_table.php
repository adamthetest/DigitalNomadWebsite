<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('coworking_spaces') && ! Schema::hasColumn('coworking_spaces', 'is_featured')) {
            Schema::table('coworking_spaces', function (Blueprint $table) {
                $table->boolean('is_featured')->default(false)->after('is_active');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('coworking_spaces') && Schema::hasColumn('coworking_spaces', 'is_featured')) {
            Schema::table('coworking_spaces', function (Blueprint $table) {
                $table->dropColumn('is_featured');
            });
        }
    }
};



