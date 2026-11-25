<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApprovedMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && !auth()->user()->isAdmin()) {
            if (auth()->user()->account_status === 'pending') {
                auth()->logout();
                return redirect()->route('login')->with('error', 'Your account is pending admin approval.');
            }
            if (auth()->user()->account_status === 'rejected') {
                auth()->logout();
                return redirect()->route('login')->with('error', 'Your account has been rejected. Please contact admin.');
            }
        }
        return $next($request);
    }
}
