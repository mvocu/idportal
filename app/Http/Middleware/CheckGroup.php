<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;

class CheckGroup
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $group)
    {
        $user = $request->user();
        if(!$user) {
            throw new AuthenticationException("Unauthenticated.", [], "/");
        }
        if(!in_array($group, $user->getGroupNames())) {
            throw new AuthorizationException("You are not authorized to access this page.");
        }
        return $next($request);
    }
}
