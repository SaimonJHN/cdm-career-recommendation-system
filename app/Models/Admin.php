<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = ['name', 'email', 'google_id', 'profile_picture', 'role', 'status', 'email_verified_at', 'last_login_at'];
    protected $casts = ['email_verified_at' => 'datetime', 'last_login_at' => 'datetime'];

    public function activityLogs()
    {
        return $this->hasMany(AdminActivityLog::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }
}
