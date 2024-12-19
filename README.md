composer create-project laravel/laravel:^9.0 laravel-ajax-crud

php artisan make:controller PostController --resource

php artisan make:model Post -mcr


php artisan route:list

php artisan make:controller VPostController -mcr

php artisan make:migration add_skill_to_posts_table --table=posts

php artisan migrate


php artisan make:middleware IsAdmin


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
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->is_admin) { // Replace 'is_admin' with your actual column name
            return $next($request);
        }

        return redirect('/')->with('error', 'You do not have admin access.');
    }
}
