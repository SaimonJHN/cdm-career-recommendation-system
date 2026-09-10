<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\AuthenticateSession;

class TrimStrings extends \Illuminate\Foundation\Http\Middleware\TrimStrings
{
    protected $except = [
        'password',
        'password_confirmation',
    ];
}
