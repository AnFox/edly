<?php

namespace App\Auth\Middleware;

use App\Contracts\Auth\Middleware\MustVerifyPhone;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Redirect;

/**
 * Class EnsurePhoneIsVerified
 * @package App\Auth\Middleware
 */
class EnsurePhoneIsVerified
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
            ($request->user() instanceof MustVerifyPhone
                && $request->user()->phone
                && $request->user()->requiresPhoneVerification()
                && !$request->user()->hasVerifiedPhone())) {
            return $request->expectsJson()
                ? abort(403, __('auth.phone_is_not_verified'))
                : Redirect::route($redirectToRoute ?: 'verification.notice');
        }

        return $next($request);
    }
}
