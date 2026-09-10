<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/';

    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));
        });
    }

    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(180)->by(get_class($request->user()).':'.$request->user()->id)
                : Limit::perMinute(config('services.campus_auth_per_minute', 2000))->by('guest:'.$request->ip());
        });

        RateLimiter::for('auth-register', function (Request $request) {
            return $this->accountLimits($request, 'register', 3);
        });

        RateLimiter::for('auth-login', function (Request $request) {
            return $this->accountLimits($request, 'login', 5);
        });

        RateLimiter::for('auth-otp-verify', function (Request $request) {
            return $this->accountLimits($request, 'otp-verify', 10);
        });

        RateLimiter::for('auth-otp-resend', function (Request $request) {
            return $this->accountLimits($request, 'otp-resend', 5);
        });

        RateLimiter::for('auth-google', function (Request $request) {
            return [Limit::perMinute(config('services.campus_auth_per_minute', 2000))->by('google-ip:'.$request->ip()), Limit::perMinute(5)->by('google-token:'.hash('sha256', (string) $request->input('google_token')))];
        });
    }

    private function accountLimits(Request $request, string $purpose, int $count): array
    {
        $identity = strtolower(trim((string) ($request->input('email') ?: $request->input('challenge_id') ?: $request->ip())));
        return [Limit::perMinute($count)->by($purpose.':'.hash('sha256', $identity)),
            Limit::perMinute(config('services.campus_auth_per_minute', 2000))->by($purpose.':ip:'.$request->ip())];
    }
}
