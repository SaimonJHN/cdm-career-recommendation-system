<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->string('institute')->nullable()->after('name');
            $table->json('career_paths')->nullable()->after('subjects');
            $table->string('image_path')->nullable()->after('career_paths');
            $table->string('program_type')->default('degree')->after('image_path');
            $table->boolean('is_active')->default(true)->after('program_type');
            $table->boolean('is_recommendable')->default(true)->after('is_active');
            $table->unsignedInteger('display_order')->default(0)->after('is_recommendable');
            $table->json('recommendation_profile')->nullable()->after('display_order');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['institute', 'career_paths', 'image_path', 'program_type', 'is_active', 'is_recommendable', 'display_order', 'recommendation_profile']);
        });
    }
};
