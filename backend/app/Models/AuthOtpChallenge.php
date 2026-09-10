<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuthOtpChallenge extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'student_id',
        'email',
        'purpose',
        'registration_payload',
        'otp_hash',
        'attempts_remaining',
        'resend_count',
        'expires_at',
        'last_sent_at',
        'consumed_at',
    ];

    protected $hidden = [
        'registration_payload',
        'otp_hash',
    ];

    protected $casts = [
        'student_id' => 'integer',
        'attempts_remaining' => 'integer',
        'resend_count' => 'integer',
        'expires_at' => 'datetime',
        'last_sent_at' => 'datetime',
        'consumed_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
