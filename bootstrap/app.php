<?php

use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsureCandidate;
use App\Http\Middleware\EnsureCandidateHasProfile;
use App\Http\Middleware\EnsureGroupAdmin;
use App\Http\Middleware\EnsureGroupAdminHasProfile;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
        ]);

        $middleware->alias([
            'admin' => EnsureAdmin::class,
            'candidate' => EnsureCandidate::class,
            'candidate.profile' => EnsureCandidateHasProfile::class,
            'group_admin' => EnsureGroupAdmin::class,
            'group_admin.profile' => EnsureGroupAdminHasProfile::class,
        ]);

        $middleware->redirectGuestsTo('/login');
        $middleware->redirectUsersTo(function (Request $request) {
            return $request->user()?->homeRoute() ?? '/';
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
