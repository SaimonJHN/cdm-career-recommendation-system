<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminActivityLog extends Model
{
    protected $fillable = ['admin_id', 'action', 'subject_type', 'subject_id', 'details', 'ip_address'];
    protected $casts = ['details' => 'array'];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
