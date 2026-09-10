<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamResult extends Model
{
    protected $appends = ['official_outcome', 'attempt_number', 'result_version'];

    public function getResultVersionAttribute(): string
    {
        return \App\Http\Controllers\ResultReviewController::version($this);
    }

    public function getOfficialOutcomeAttribute(): string
    {
        if ($this->official_status !== 'published') return strtoupper($this->official_status ?? 'pending');
        if ($this->registrar_pass || $this->official_score >= 75) return 'PASSED';
        return $this->attempt_number >= 2 ? 'FAILED' : 'RETAKE';
    }

    public function getAttemptNumberAttribute(): int
    {
        return (int) ($this->attributes['attempt_order'] ?? static::where('student_id', $this->student_id)->where('id', '<=', $this->id)->count());
    }
    protected $fillable = [
        'registrar_pass', 'registrar_pass_reason', 'registrar_pass_by', 'registrar_pass_at',
        'category_maximums', 'career_interests', 'recommendation_payload',
        'student_id',
        'total_score',
        'percentage',
        'official_score',
        'time_spent',
        'exam_date',
        'is_passed',
        'official_status',
        'registrar_remarks',
        'approved_by',
        'approved_at',
        'published_by',
        'published_at',
        'category_scores',
    ];

    protected $casts = [
        'registrar_pass' => 'boolean',
        'registrar_pass_at' => 'datetime',
        'category_maximums' => 'array',
        'career_interests' => 'array',
        'recommendation_payload' => 'array',
        'exam_date' => 'datetime',
        'is_passed' => 'boolean',
        'category_scores' => 'array',
        'official_score' => 'integer',
        'approved_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function allowsRetake(): bool
    {
        return $this->official_status === 'published'
            && !$this->registrar_pass
            && $this->official_score !== null
            && $this->official_score < 75;
    }

    public function examAnswers()
    {
        return $this->hasMany(ExamAnswer::class);
    }

    public function scopeEligibleForRegistrarPass($query)
    {
        return $query->where('registrar_pass', false)
            ->where('is_passed', false)
            ->where(fn ($q) => $q->whereNull('official_score')->orWhere('official_score', '<', 75))
            ->whereRaw('(SELECT COUNT(*) FROM exam_results AS attempts WHERE attempts.student_id = exam_results.student_id) = 2')
            ->whereRaw('exam_results.id = (SELECT MAX(latest.id) FROM exam_results AS latest WHERE latest.student_id = exam_results.student_id)')
            ->whereExists(function ($q) {
                $q->selectRaw('1')->from('exam_results AS first_attempt')
                    ->whereColumn('first_attempt.student_id', 'exam_results.student_id')
                    ->whereColumn('first_attempt.id', '<', 'exam_results.id')
                    ->where('first_attempt.official_status', 'published')
                    ->where('first_attempt.official_score', '<', 75)
                    ->where('first_attempt.registrar_pass', false);
            });
    }
}
