<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('password')->nullable();
            $table->string('google_id')->nullable()->unique();
            $table->string('student_number')->unique();
            $table->string('admission_year');
            $table->date('date_of_birth')->nullable();
            $table->string('profile_picture')->nullable();
            $table->boolean('is_google_account')->default(false);
            $table->boolean('exam_taken')->default(false);
            $table->integer('exam_score')->nullable();
            $table->string('recommended_program')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
