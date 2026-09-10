<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Schema, DB};
return new class extends Migration {
    public function up(): void
    {
        Schema::create('exam_session_questions', function (Blueprint $table) {
            $table->uuid('exam_session_id');
            $table->foreign('exam_session_id')->references('id')->on('exam_sessions')->cascadeOnDelete();
            $table->foreignId('exam_question_id')->constrained('exam_questions')->restrictOnDelete();
            $table->primary(['exam_session_id', 'exam_question_id']);
        });
        DB::table('exam_sessions')->orderBy('id')->chunkById(100, function ($sessions) {
            foreach ($sessions as $session) foreach (json_decode($session->questions, true) as $question) {
                if (DB::table('exam_questions')->where('id', $question['id'])->exists()) DB::table('exam_session_questions')->insert(['exam_session_id' => $session->id, 'exam_question_id' => $question['id']]);
            }
        });
    }
    public function down(): void { Schema::dropIfExists('exam_session_questions'); }
};
