<?php

// Create middleware: php artisan make:middleware ForcePasswordChange

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip if user is not authenticated
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Skip if already on password change page or logout
        if ($request->routeIs('password.change.form') ||
            $request->routeIs('password.change') ||
            $request->routeIs('logout')) {
            return $next($request);
        }

        // Skip for AJAX requests to avoid breaking functionality
        if ($request->ajax() || $request->wantsJson()) {
            return $next($request);
        }

        // Check if password needs to be changed
        if (!$user->password_changed) {
            return redirect()->route('password.change.form')
                ->with('warning', 'You must change your password before continuing.');
        }

        return $next($request);
    }
}

// Register middleware in app/Http/Kernel.php
// In the $middlewareAliases array, add:
// 'force.password.change' => \App\Http\Middleware\ForcePasswordChange::class,
