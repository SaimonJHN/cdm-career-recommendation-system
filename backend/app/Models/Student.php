<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Student extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'google_id',
        'student_number',
        'admission_year',
        'date_of_birth',
        'profile_picture',
        'is_google_account',
        'account_status',
        'exam_taken',
        'exam_score',
        'recommended_program',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'full_name',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'exam_taken' => 'boolean',
        'exam_score' => 'integer',
    ];

    public function examResults()
    {
        return $this->hasMany(ExamResult::class);
    }

    public function recommendation()
    {
        return $this->hasOne(Recommendation::class);
    }

    public function trustedDevices()
    {
        return $this->hasMany(TrustedDevice::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(StudentActivityLog::class);
    }

    public function getFullNameAttribute()
    {
        $first = trim((string) ($this->first_name ?? ''));
        $last = trim((string) ($this->last_name ?? ''));
        return trim($first . ' ' . $last) ?: 'Student';
    }
}
