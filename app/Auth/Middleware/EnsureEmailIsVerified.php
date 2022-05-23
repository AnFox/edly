<?php

namespace App\Auth\Middleware;

use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Redirect;

/**
 * Class EnsureEmailIsVerified
 * @package App\Auth\Middleware
 */
class EnsureEmailIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param string|null $redirectToRoute
     * @return Response|RedirectResponse
     */
    public function handle($request, Closure $next, $redirectToRoute = null)
    {
        if (!$request->user() ||
            ($request->user() instanceof MustVerifyEmail && $request->user()->email &&
                !$request->user()->hasVerifiedEmail())) {
            return $request->expectsJson()
                ? abort(403, __('auth.email_is_not_verified'))
                : Redirect::route($redirectToRoute ?: 'verification.notice');
        }

        return $next($request);
    }
}
