<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrustedDevice extends Model
{
    protected $fillable = ['student_id', 'token_hash', 'user_agent', 'last_used_at', 'expires_at'];
    protected $hidden = ['token_hash'];
    protected $casts = ['last_used_at' => 'datetime', 'expires_at' => 'datetime'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
