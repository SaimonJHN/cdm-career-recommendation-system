<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auth_otp_challenges', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('student_id')->nullable()->constrained('students')->cascadeOnDelete();
            $table->string('email', 191);
            $table->string('purpose', 20);
            $table->text('registration_payload')->nullable();
            $table->string('otp_hash');
            $table->unsignedTinyInteger('attempts_remaining');
            $table->unsignedTinyInteger('resend_count')->default(0);
            $table->dateTime('expires_at');
            $table->dateTime('last_sent_at');
            $table->dateTime('consumed_at')->nullable();
            $table->timestamps();

            $table->index(['email', 'purpose']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth_otp_challenges');
    }
};
