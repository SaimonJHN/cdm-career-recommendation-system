<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentActivityLog extends Model
{
    protected $fillable = ['student_id', 'action', 'auth_method', 'ip_address', 'user_agent'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
