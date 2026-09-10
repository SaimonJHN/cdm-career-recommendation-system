<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exam_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('attempt_number');
            $table->string('bank_version', 64);
            $table->json('questions');
            $table->json('answers');
            $table->unsignedInteger('revision')->default(0);
            $table->unsignedInteger('position')->default(0);
            $table->dateTime('started_at');
            $table->dateTime('deadline')->index();
            $table->foreignId('exam_result_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->unique(['student_id', 'attempt_number']);
        });
        Schema::create('portal_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('message');
            $table->string('link')->default('/results');
            $table->timestamp('read_at')->nullable();
            $table->timestamp('emailed_at')->nullable();
            $table->unsignedInteger('email_attempts')->default(0);
            $table->timestamp('email_retry_at')->nullable()->index();
            $table->timestamps();
        });
        Schema::create('student_password_resets', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token_hash');
            $table->dateTime('expires_at');
        });
        Schema::create('result_import_previews', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('admin_id')->constrained('admins')->cascadeOnDelete();
            $table->json('rows');
            $table->dateTime('expires_at');
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('result_import_previews');
        Schema::dropIfExists('student_password_resets');
        Schema::dropIfExists('portal_notifications');
        Schema::dropIfExists('exam_sessions');
    }
};
