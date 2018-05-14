<?php

namespace RBennett\JWTGuard\Middleware;

use RBennett\JWTGuard\Exceptions\MissingScopeException;
use Closure;
use Illuminate\Auth\AuthenticationException;

class JWTScopes
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param array $scopes
     * @return mixed
     * @throws AuthenticationException
     * @throws MissingScopeException
     */
    public function handle($request, Closure $next, ...$scopes)
    {
        if (! $request->user()) {
            throw new AuthenticationException();
        }

        foreach ($scopes as $scope) {
            if (! $request->user()->tokenCan($scope)) {
                throw new MissingScopeException($scope);
            }
        }

        return $next($request);
    }
}
