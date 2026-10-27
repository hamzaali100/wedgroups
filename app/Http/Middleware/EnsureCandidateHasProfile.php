<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCandidateHasProfile
{
    /**
     * Candidate features other than profile creation require a profile row.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->candidate === null) {
            return redirect()->route('candidate.profile.create');
        }

        return $next($request);
    }
}
