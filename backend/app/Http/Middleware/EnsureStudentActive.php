<?php

namespace App\Http\Middleware;

use App\Models\Student;
use Closure;
use Illuminate\Http\Request;

class EnsureStudentActive
{
    public function handle(Request $request, Closure $next)
    {
        $student = $request->user();
        if (!$student instanceof Student || $student->account_status !== 'active') {
            $student?->currentAccessToken()?->delete();
            return response()->json(['success' => false, 'message' => 'This student account is not active.'], 403);
        }
        return $next($request);
    }
}
