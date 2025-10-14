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
        Schema::table('users', function (Blueprint $table) {
            $table->text('bio')->nullable()->after('email');
            $table->string('location')->nullable()->after('bio');
            $table->string('profile_image')->nullable()->after('location');
            $table->string('website')->nullable()->after('profile_image');
            $table->string('twitter')->nullable()->after('website');
            $table->string('instagram')->nullable()->after('twitter');
            $table->string('linkedin')->nullable()->after('instagram');
            $table->string('github')->nullable()->after('linkedin');
            $table->boolean('is_public')->default(true)->after('github');
            $table->string('timezone')->nullable()->after('is_public');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'bio',
                'location',
                'profile_image',
                'website',
                'twitter',
                'instagram',
                'linkedin',
                'github',
                'is_public',
                'timezone',
            ]);
        });
    }
};
