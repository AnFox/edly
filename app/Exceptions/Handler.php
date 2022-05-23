<?php

namespace App\Exceptions;

use Exception;
use GuzzleHttp\Exception\ServerException;
use Illion\Service\Exceptions\Auth\AuthServiceHttpException;
use Illion\Service\Exceptions\Auth\InvalidCredentialsHttpException;
use Illion\Service\Exceptions\UserServiceHttpException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Exception\SuspiciousOperationException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * A list of the internal exception types that should not be reported.
     *
     * @var array
     */
    protected $internalDontReport = [
        AuthenticationException::class,
//        AuthorizationException::class,
//        HttpException::class,
        HttpResponseException::class,
        ModelNotFoundException::class,
        SuspiciousOperationException::class,
        TokenMismatchException::class,
//        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * @param Throwable $exception
     * @return void
     *
     * @throws Exception
     */
    public function report(Throwable $exception)
    {
        if (app()->bound('sentry') && $this->shouldReport($exception)) {
            app('sentry')->captureException($exception);
        }

        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param Request $request
     * @param Throwable $exception
     * @return Response
     *
     * @throws Throwable
     */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof ValidationException) {
            return response()->json(['message' => 'Форма заполнена некорректно', 'code' => $exception->getCode(), 'errors' => $exception->validator->getMessageBag()], 422);
        }

        if ($exception instanceof AttemptToRegisterAlreadyRegisteredUser) {
            return response()->json(['message' => $exception->getMessage(), 'code' => $exception->getCode()], 422);
        }

        if ($exception instanceof ThrottleRequestsException) {
            Log::notice('Слишком много запросов');

            return response()->json([
                'message' => Lang::get('request.throttle'),
            ], 429);
        }

        if ($exception instanceof InvalidCredentialsHttpException
            || $exception instanceof AuthServiceHttpException) {
            return response()->json([
                'message' => Lang::get('auth.login_failed'),
            ], $exception->getStatusCode());
        }

        if ($exception instanceof AuthServiceHttpException) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], $exception->getStatusCode());
        }

        if ($exception instanceof UserServiceHttpException) {
            if ($exception->getStatusCode() === 429) {
                Log::notice('Слишком много запросов к UserService');

                return response()->json([
                    'message' => Lang::get('request.throttle'),
                ], 429);
            }

            return response()->json($exception->getMessage(), $exception->getStatusCode() ?: 422);
        }

        if ($exception instanceof ServerException) {
            return response()->json(['message' => 'Непредвиденная ошибка сервера.'], $exception->getCode() ?: 422);
        }

        return parent::render($request, $exception);
    }
}
