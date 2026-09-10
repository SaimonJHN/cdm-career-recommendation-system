<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_questions', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('difficulty_level');
        });

        DB::table('exam_questions')->update(['is_active' => false]);

        $now = now();
        $questions = [
            ['question_number' => 1, 'question_text' => 'A laptop originally costs ₱30,000. It is discounted by 15%. What is the sale price?', 'category' => 'General Mathematics', 'option_a' => '₱24,500', 'option_b' => '₱25,500', 'option_c' => '₱26,500', 'option_d' => '₱27,000', 'correct_answer' => 'B'],
            ['question_number' => 2, 'question_text' => 'Which process allows plants to convert light energy into chemical energy stored in glucose?', 'category' => 'Science', 'option_a' => 'Cellular respiration', 'option_b' => 'Transpiration', 'option_c' => 'Photosynthesis', 'option_d' => 'Fermentation', 'correct_answer' => 'C'],
            ['question_number' => 3, 'question_text' => 'A report states that students who sleep at least eight hours generally perform better, but sleep alone does not guarantee high grades. Which conclusion is best supported?', 'category' => 'Reading Comprehension', 'option_a' => 'Sleep is the only factor affecting grades.', 'option_b' => 'Adequate sleep may support academic performance.', 'option_c' => 'Every high-performing student sleeps eight hours.', 'option_d' => 'Studying has no effect on academic performance.', 'correct_answer' => 'B'],
            ['question_number' => 4, 'question_text' => 'All scholarship applicants submitted their requirements. Mia did not submit her requirements. What can logically be concluded?', 'category' => 'Logical Reasoning', 'option_a' => 'Mia received the scholarship.', 'option_b' => 'Mia is not a scholarship applicant.', 'option_c' => 'Mia submitted late.', 'option_d' => 'No conclusion can be made.', 'correct_answer' => 'B'],
            ['question_number' => 5, 'question_text' => 'Which practice best protects an online student account?', 'category' => 'Digital Literacy', 'option_a' => 'Reusing one short password everywhere', 'option_b' => 'Sharing the password with classmates', 'option_c' => 'Using a unique password and multi-factor authentication', 'option_d' => 'Saving the password on a public computer', 'correct_answer' => 'C'],
        ];

        foreach ($questions as $question) {
            DB::table('exam_questions')->updateOrInsert(
                ['question_number' => $question['question_number']],
                array_merge($question, ['difficulty_level' => 'medium', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now])
            );
        }
    }

    public function down(): void
    {
        DB::table('exam_questions')->update(['is_active' => true]);
        Schema::table('exam_questions', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
