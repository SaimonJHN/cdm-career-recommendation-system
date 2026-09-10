<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\RedirectIfAuthenticated as Middleware;

class RedirectIfAuthenticated extends Middleware
{
    protected function redirectTo($request)
    {
        if ($response = parent::redirectTo($request)) {
            return $response;
        }

        return route('home');
    }
}
