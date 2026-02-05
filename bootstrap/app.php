<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, Request $request) {
            if (!$request->is('api/*') && !$request->expectsJson()) {
                return null;
            }

            $status = match (true) {
                $e instanceof ModelNotFoundException => 404,
                $e instanceof NotFoundHttpException => 404,
                $e instanceof AuthenticationException => 401,
                $e instanceof AuthorizationException => 403,
                $e instanceof ValidationException => 422,
                $e instanceof MethodNotAllowedHttpException => 405,
                default => 500,
            };

            $message = match (true) {
                $e instanceof ModelNotFoundException => 'Resource not found',
                $e instanceof NotFoundHttpException => 'Endpoint not found',
                $e instanceof AuthenticationException => 'Unauthenticated',
                $e instanceof AuthorizationException => 'Forbidden',
                $e instanceof ValidationException => 'Validation failed',
                $e instanceof MethodNotAllowedHttpException => 'Method not allowed',
                default => 'Server error',
            };

            $response = ['error' => $message];

            if ($e instanceof ValidationException) {
                $response['errors'] = $e->errors();
            }

            return response()->json($response, $status);
        });
    })->create();
