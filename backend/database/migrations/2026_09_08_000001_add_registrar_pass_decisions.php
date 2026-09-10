<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('exam_results', function (Blueprint $table) {
            $table->boolean('registrar_pass')->default(false);
            $table->text('registrar_pass_reason')->nullable();
            $table->foreignId('registrar_pass_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('registrar_pass_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('exam_results', function (Blueprint $table) {
            $table->dropForeign(['registrar_pass_by']);
            $table->dropColumn(['registrar_pass', 'registrar_pass_reason', 'registrar_pass_by', 'registrar_pass_at']);
        });
    }
};
