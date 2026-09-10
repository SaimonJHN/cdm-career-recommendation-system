<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $questions = [
            ['Logical Reasoning', 'A bus leaves every 15 minutes starting at 8:00 AM. What is the next departure after 8:35 AM?', '8:40 AM', '8:45 AM', '8:50 AM', '9:00 AM', 'B'],
            ['Logical Reasoning', 'Ana is ahead of Ben in a queue. Ben is ahead of Carlo. Who is last among these three?', 'Ana', 'Ben', 'Carlo', 'They are in the same position', 'C'],
            ['Logical Reasoning', 'What number comes next in the pattern: 3, 6, 12, 24, ...?', '27', '30', '36', '48', 'D'],
            ['Logical Reasoning', 'A room can be used only with a reservation. Liza has no reservation. According to this rule, may she use the room?', 'Yes, because it is empty', 'Yes, if she arrives early', 'No, because a reservation is required', 'Yes, if she stays briefly', 'C'],
            ['Digital Literacy', 'An unexpected message asks you to enter your account password using a link. What is the safest response?', 'Enter the password immediately', 'Forward your password to a friend', 'Verify the request using the official website or a known contact', 'Reply with your password', 'C'],
            ['Digital Literacy', 'Before sharing an online news story, what should you do first?', 'Check its source, date, and supporting evidence', 'Share it if the headline is exciting', 'Assume it is true because many people shared it', 'Remove the source name', 'A'],
            ['Digital Literacy', 'You finish using your email on a shared computer. What should you do before leaving?', 'Leave the account signed in', 'Sign out of the account', 'Share the password with the next user', 'Save your password in the browser', 'B'],
            ['Digital Literacy', 'Which practice helps you recover a document if your device stops working?', 'Keep only one copy on that device', 'Rename the document every day', 'Delete older copies without checking them', 'Keep a backup on a separate device or trusted cloud service', 'D'],
        ];
        DB::transaction(function () use ($questions) {
            $number = (int) DB::table('exam_questions')->max('question_number');
            foreach ($questions as [$category, $text, $a, $b, $c, $d, $answer]) {
                if (DB::table('exam_questions')->where('question_text', $text)->exists()) continue;
                DB::table('exam_questions')->insert([
                    'question_number' => ++$number, 'category' => $category, 'question_text' => $text,
                    'option_a' => $a, 'option_b' => $b, 'option_c' => $c, 'option_d' => $d,
                    'correct_answer' => $answer, 'difficulty_level' => 'easy', 'is_active' => true,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        });
    }

    public function down(): void
    {
        // Retain questions that may be referenced by submitted exam answers.
    }
};
