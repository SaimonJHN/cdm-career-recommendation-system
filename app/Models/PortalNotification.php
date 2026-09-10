<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PortalNotification extends Model
{
    protected $guarded = [];
    protected $casts = ['read_at' => 'datetime', 'emailed_at' => 'datetime', 'email_retry_at' => 'datetime'];
}
