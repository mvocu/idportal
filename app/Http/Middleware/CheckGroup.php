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
    public function handle($request, Closure $next)
    {
        $user = $request->user();
        if(!$user) {
            throw new AuthenticationException("Unauthenticated.", [], "/");
        }
        $pass = false;
        $groups = array_slice(func_get_args(), 2);
        if(is_array($groups) && !empty($groups)) {
            foreach($groups as $group) {
                if(in_array($group, $user->getGroupNames())) {
                    $pass = true;
                }
            }
        }
        if(!$pass) {
            throw new AuthorizationException("You are not authorized to access this page.");
        }
        return $next($request);
    }
}
