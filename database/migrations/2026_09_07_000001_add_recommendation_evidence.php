<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('exam_results', function (Blueprint $table) {
            $table->json('category_maximums')->nullable();
            $table->json('career_interests')->nullable();
            $table->json('recommendation_payload')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('exam_results', function (Blueprint $table) {
            $table->dropColumn(['category_maximums', 'career_interests', 'recommendation_payload']);
        });
    }
};
