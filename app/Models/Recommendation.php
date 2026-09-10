<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recommendation extends Model
{
    protected $fillable = [
        'student_id',
        'course_id',
        'confidence_score',
        'reasoning',
        'alternative_recommendations',
    ];

    protected $casts = [
        'confidence_score' => 'float',
        'alternative_recommendations' => 'array',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
