<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::transaction(function () {
            // Restore the retained 100-question bank disabled by the five-question migration.
            // A fresh database may only contain the five sample questions.
            $bank = DB::table('exam_questions')->whereBetween('question_number', [1, 100]);
            if ((clone $bank)->count() !== 100) return;
            (clone $bank)->update(['is_active' => true]);
            (clone $bank)->where('category', 'Technical Aptitude')->update(['category' => 'Digital Literacy']);
        });
    }

    public function down(): void
    {
        // Preserve the restored question bank and any subsequent exam-manager edits.
    }
};
