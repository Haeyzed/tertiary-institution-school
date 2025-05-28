<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Add any middleware configurations here
    })
    ->withExceptions(function (Exceptions $exceptions) {

        // Handle Authentication Exceptions
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                Log::warning('Authentication failed', [
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated. Please login to access this resource.',
                    'errors' => null,
                ], 401);
            }
        });

        // Handle Authorization Exceptions
        $exceptions->render(function (AuthorizationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                Log::warning('Authorization failed', [
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'user' => $request->user()?->id,
                    'message' => $e->getMessage(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. You do not have permission to perform this action.',
                    'errors' => null,
                ], 403);
            }
        });

        // Handle Validation Exceptions
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                Log::info('Validation failed', [
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'errors' => $e->errors(),
                    'input' => $request->except(['password', 'password_confirmation', 'token']),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'The given data was invalid.',
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        // Handle Model Not Found Exceptions
        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                $model = class_basename($e->getModel());

                Log::info('Model not found', [
                    'model' => $model,
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => "{$model} not found.",
                    'errors' => null,
                ], 404);
            }
        });

        // Handle Route Not Found Exceptions
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                Log::info('Route not found', [
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'ip' => $request->ip(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'The requested resource was not found.',
                    'errors' => null,
                ], 404);
            }
        });

        // Handle Method Not Allowed Exceptions
        $exceptions->render(function (MethodNotAllowedHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                Log::info('Method not allowed', [
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'allowed_methods' => $e->getHeaders()['Allow'] ?? 'Unknown',
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'The ' . $request->method() . ' method is not allowed for this route.',
                    'errors' => null,
                ], 405);
            }
        });

        // Handle Rate Limiting Exceptions
        $exceptions->render(function (TooManyRequestsHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                $retryAfter = $e->getHeaders()['Retry-After'] ?? null;

                Log::warning('Rate limit exceeded', [
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'ip' => $request->ip(),
                    'retry_after' => $retryAfter,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Too many requests. Please try again later.',
                    'errors' => null,
                    'retry_after' => $retryAfter,
                ], 429);
            }
        });

        // Handle Database Query Exceptions
        $exceptions->render(function (QueryException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                Log::error('Database query error', [
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'sql' => $e->getSql(),
                    'bindings' => $e->getBindings(),
                    'message' => $e->getMessage(),
                ]);

                // Check for specific database errors
                $errorCode = $e->errorInfo[1] ?? null;
                $message = 'A database error occurred.';

                switch ($errorCode) {
                    case 1062: // Duplicate entry
                        $message = 'The data you are trying to save already exists.';
                        break;
                    case 1452: // Foreign key constraint
                        $message = 'The operation cannot be completed due to data dependencies.';
                        break;
                    case 1054: // Unknown column
                        $message = 'Invalid data field specified.';
                        break;
                }

                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'errors' => null,
                ], 500);
            }
        });

        // Handle HTTP Exceptions (4xx, 5xx)
        $exceptions->render(function (HttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                $statusCode = $e->getStatusCode();

                Log::warning('HTTP exception', [
                    'status_code' => $statusCode,
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'message' => $e->getMessage(),
                ]);

                $messages = [
                    400 => 'Bad request. Please check your input.',
                    401 => 'Unauthenticated. Please login to access this resource.',
                    403 => 'Access denied. You do not have permission to perform this action.',
                    404 => 'The requested resource was not found.',
                    405 => 'Method not allowed for this route.',
                    408 => 'Request timeout. Please try again.',
                    409 => 'Conflict. The request could not be completed due to a conflict.',
                    410 => 'The requested resource is no longer available.',
                    422 => 'The given data was invalid.',
                    429 => 'Too many requests. Please try again later.',
                    500 => 'Internal server error. Please try again later.',
                    502 => 'Bad gateway. The server is temporarily unavailable.',
                    503 => 'Service unavailable. Please try again later.',
                    504 => 'Gateway timeout. Please try again later.',
                ];

                $message = $e->getMessage() ?: ($messages[$statusCode] ?? 'An error occurred.');

                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'errors' => null,
                ], $statusCode);
            }
        });

        // Handle JWT Exceptions (if using JWT)
        $exceptions->render(function (TokenExpiredException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                Log::info('JWT token expired', [
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'ip' => $request->ip(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Token has expired. Please login again.',
                    'errors' => null,
                ], 401);
            }
        });

        $exceptions->render(function (TokenInvalidException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                Log::warning('JWT token invalid', [
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'ip' => $request->ip(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Token is invalid. Please login again.',
                    'errors' => null,
                ], 401);
            }
        });

        $exceptions->render(function (JWTException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                Log::error('JWT exception', [
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'message' => $e->getMessage(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Token error. Please login again.',
                    'errors' => null,
                ], 401);
            }
        });

        // Handle General Exceptions (Catch-all)
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                // Log the full exception for debugging
                Log::error('Unhandled exception', [
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                    'input' => $request->except(['password', 'password_confirmation', 'token']),
                ]);

                // Don't expose internal errors in production
                $message = app()->environment('production')
                    ? 'An unexpected error occurred. Please try again later.'
                    : $e->getMessage();

                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'errors' => null,
                ], 500);
            }
        });

    })->create();
