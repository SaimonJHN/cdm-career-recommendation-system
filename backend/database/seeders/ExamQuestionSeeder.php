<?php

namespace Database\Seeders;

use App\Models\ExamQuestion;
use Illuminate\Database\Seeder;

class ExamQuestionSeeder extends Seeder
{
    public function run(): void
    {
        $questions = [
            ['category' => 'General Mathematics', 'text' => 'A laptop originally costs ₱30,000. It is discounted by 15%. What is the sale price?', 'a' => '₱24,500', 'b' => '₱25,500', 'c' => '₱26,500', 'd' => '₱27,000', 'answer' => 'B'],
            ['category' => 'Science', 'text' => 'Which process allows plants to convert light energy into chemical energy stored in glucose?', 'a' => 'Cellular respiration', 'b' => 'Transpiration', 'c' => 'Photosynthesis', 'd' => 'Fermentation', 'answer' => 'C'],
            ['category' => 'Reading Comprehension', 'text' => 'A report states that students who sleep at least eight hours generally perform better, but sleep alone does not guarantee high grades. Which conclusion is best supported?', 'a' => 'Sleep is the only factor affecting grades.', 'b' => 'Adequate sleep may support academic performance.', 'c' => 'Every high-performing student sleeps eight hours.', 'd' => 'Studying has no effect on academic performance.', 'answer' => 'B'],
            ['category' => 'Logical Reasoning', 'text' => 'All scholarship applicants submitted their requirements. Mia did not submit her requirements. What can logically be concluded?', 'a' => 'Mia received the scholarship.', 'b' => 'Mia is not a scholarship applicant.', 'c' => 'Mia submitted late.', 'd' => 'No conclusion can be made.', 'answer' => 'B'],
            ['category' => 'Digital Literacy', 'text' => 'Which practice best protects an online student account?', 'a' => 'Reusing one short password everywhere', 'b' => 'Sharing the password with classmates', 'c' => 'Using a unique password and multi-factor authentication', 'd' => 'Saving the password on a public computer', 'answer' => 'C'],
        ];

        // Preserve the question bank maintained by exam managers.
        if (ExamQuestion::query()->exists()) {
            return;
        }
        foreach ($questions as $index => $question) {
            ExamQuestion::updateOrCreate(
                ['question_number' => $index + 1],
                [
                    'question_text' => $question['text'], 'category' => $question['category'],
                    'option_a' => $question['a'], 'option_b' => $question['b'],
                    'option_c' => $question['c'], 'option_d' => $question['d'],
                    'correct_answer' => $question['answer'], 'difficulty_level' => 'medium', 'is_active' => true,
                ]
            );
        }
    }
}
