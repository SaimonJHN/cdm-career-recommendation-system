<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;
use Illuminate\Http\Request;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $admin = $request->user();
        if (!$admin instanceof Admin || $admin->status !== 'active') {
            return response()->json(['success' => false, 'message' => 'Administrator access is required.'], 403);
        }

        if ($roles && !$admin->isSuperAdmin() && !in_array($admin->role, $roles, true)) {
            return response()->json(['success' => false, 'message' => 'You do not have permission to perform this action.'], 403);
        }

        return $next($request);
    }
}
