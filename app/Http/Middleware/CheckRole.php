<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    private function isAdmin($roleId) {
        // Define admin role IDs
        $adminRoles = [100, 101, 999, 888];
        return in_array($roleId, $adminRoles);
    }

    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!$request->user()) {
            abort(403, 'Unauthorized action.');
        }

        $userRoleId = $request->user()->role;
        
        foreach ($roles as $role) {
            if ($role === 'admin' && $this->isAdmin($userRoleId)) {
                return $next($request);
            }
            if ($role === 'user' && !$this->isAdmin($userRoleId)) {
                return $next($request);
            }
        }

        abort(403, 'Unauthorized action.');
    }
} 