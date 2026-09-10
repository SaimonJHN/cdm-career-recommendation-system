<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_results', function (Blueprint $table) {
            $table->unsignedTinyInteger('official_score')->nullable()->after('percentage');
            $table->string('official_status', 20)->default('pending')->after('is_passed');
            $table->text('registrar_remarks')->nullable()->after('official_status');
            $table->foreignId('approved_by')->nullable()->after('registrar_remarks')->constrained('admins')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->foreignId('published_by')->nullable()->after('approved_at')->constrained('admins')->nullOnDelete();
            $table->timestamp('published_at')->nullable()->after('published_by');
            $table->index('official_status');
        });
    }

    public function down(): void
    {
        Schema::table('exam_results', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['published_by']);
            $table->dropIndex(['official_status']);
            $table->dropColumn(['official_score', 'official_status', 'registrar_remarks', 'approved_by', 'approved_at', 'published_by', 'published_at']);
        });
    }
};
