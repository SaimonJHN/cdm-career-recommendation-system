<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamQuestion extends Model
{
    protected $fillable = [
        'question_number',
        'question_text',
        'category',
        'option_a',
        'option_b',
        'option_c',
        'option_d',
        'correct_answer',
        'difficulty_level',
        'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function examResults()
    {
        return $this->belongsToMany(ExamResult::class, 'exam_answers')
                    ->withPivot('student_answer', 'is_correct');
    }
}
