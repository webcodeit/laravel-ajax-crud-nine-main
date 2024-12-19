<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */

    public function handle(Request $request, Closure $next, $roles)
    {
       
        $roleArray = explode('|', $roles) ?? [];

        // if (in_array(Auth::user()->role, $roleArray)) { // Replace 'role' with your actual attribute name
        if (in_array('admin', $roleArray)) { 
            return $next($request);
        } else if (in_array('user', $roleArray)) {
            return $next($request);
        } else if ($roles == 'admin' || $roles == 'user') { 
            // if (Auth::check() && Auth::user()->is_admin) { // Replace 'is_admin' with your actual column name
            return $next($request);
        } else {
            return redirect('/')->with('error', 'You do not have admin access.');
        }

        return redirect('/')->with('error', 'You do not have admin access.');
    }
}
