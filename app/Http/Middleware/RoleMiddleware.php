<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        if (!$user) {
            abort(403);
        }

        $allowedRoles = array_filter(array_map('strtolower', $roles));
        if (empty($allowedRoles)) {
            return $next($request);
        }

        $roleChecks = [
            'user' => $user->isUser(),
            'admin' => $user->isAdmin(),
            'super_admin' => $user->isSuperAdmin(),
        ];

        $hasAccess = false;
        foreach ($allowedRoles as $role) {
            if (!empty($roleChecks[$role])) {
                $hasAccess = true;
                break;
            }
        }

        if (!$hasAccess) {
            abort(403);
        }

        return $next($request);
    }
}
