<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureGroupAdminHasProfile
{
    /**
     * Managing a group requires the group_admins profile row, because membership points at it.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->groupAdmin === null) {
            return redirect()->route('group_admin.profile.edit');
        }

        return $next($request);
    }
}
