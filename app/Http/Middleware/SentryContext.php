<?php


namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Sentry\State\Scope;

/**
 * Class SentryContext
 * @package App\Http\Middleware
 */
class SentryContext
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (auth()->check() && app()->bound('sentry')) {
            \Sentry\configureScope(function (Scope $scope): void {
                $scope->setUser([
                    'id' => auth()->user()->id,
                    'email' => auth()->user()->email,
                ]);
            });
        }

        return $next($request);
    }
}