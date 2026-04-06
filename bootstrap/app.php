<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            \App\Http\Middleware\SecurityHeaders::class,
            \App\Http\Middleware\ForceJsonResponse::class,
        ]);

        $middleware->api(append: [
            'throttle:api',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(function (Request $request) {
            return (
                $request->is('api/*')
                || $request->expectsJson()
            );
        });

        $exceptions->render(function (Throwable $e, Request $request) {
            if (! $request->is('api/*')
                && ! $request->expectsJson()
            ) {
                return null;
            }

            $status = 500;
            if ($e instanceof HttpExceptionInterface) {
                $status = $e->getStatusCode();
            }
            if ($e instanceof \Illuminate\Validation\ValidationException) {
                $status = 422;
            }
            if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                $status = 401;
            }
            if ($e instanceof \App\Exceptions\BusinessException) {
                $status = $e->getCode();
            }

            $response = [
                'error' => [
                    'message' => match ($status) {
                        401 => 'Authentication required.',
                        403 => 'You do not have permission to perform this action.',
                        404 => 'The requested resource was not found.',
                        422 => $e->getMessage(),
                        429 => 'Too many requests. Please try again later.',
                        500 => app()->hasDebugModeEnabled()
                            ? $e->getMessage()
                            : 'An internal error occurred.',
                        default => $e->getMessage(),
                    },
                ],
            ];

            if ($status === 422 && $e instanceof \Illuminate\Validation\ValidationException) {
                $response['error']['details'] = $e->errors();
            }

            if ($status === 500 && app()->hasDebugModeEnabled()) {
                $response['error']['debug'] = [
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ];
            }

            return response()->json($response, $status);
        });

    })->create();
