<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LecturerAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('lecturer')->check()) {
            return redirect()->route('login')->with('error', 'Please login to access lecturer dashboard.');
        }

        return $next($request);
    }
}
