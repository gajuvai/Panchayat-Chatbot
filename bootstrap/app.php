<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {

        // ── Model not found → 404 ─────────────────────────────────────────────
        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'The requested resource was not found.',
                ], 404);
            }
            return response()->view('errors.404', ['exception' => $e], 404);
        });

        // ── Not found (HTTP 404) ──────────────────────────────────────────────
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'The requested URL was not found.',
                ], 404);
            }
            return response()->view('errors.404', ['exception' => $e], 404);
        });

        // ── Access denied (HTTP 403) ──────────────────────────────────────────
        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'You are not authorized to perform this action.',
                ], 403);
            }
            return response()->view('errors.403', ['exception' => $e], 403);
        });

        // ── Unauthenticated → redirect to login for web, 401 for API ─────────
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Authentication required. Please sign in.',
                ], 401);
            }
            return redirect()->guest(route('login'))
                ->with('error', 'Please sign in to continue.');
        });

        // ── CSRF token mismatch → back with message ───────────────────────────
        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your session has expired. Please refresh and try again.',
                ], 419);
            }
            return response()->view('errors.419', ['exception' => $e], 419);
        });

        // ── Validation failed → JSON or back with errors ──────────────────────
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors'  => $e->errors(),
                ], 422);
            }
            // For web: let Laravel's default behaviour handle (redirects back with errors)
        });

        // ── Method not allowed (HTTP 405) ─────────────────────────────────────
        $exceptions->render(function (MethodNotAllowedHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'HTTP method not allowed for this endpoint.',
                ], 405);
            }
            return response()->view('errors.405', ['exception' => $e], 405);
        });

        // ── Rate limiting (HTTP 429) ──────────────────────────────────────────
        $exceptions->render(function (TooManyRequestsHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message'     => 'Too many requests. Please slow down.',
                    'retry_after' => $e->getHeaders()['Retry-After'] ?? null,
                ], 429);
            }
            return response()->view('errors.429', ['exception' => $e], 429);
        });

        // ── Generic HTTP exceptions (covers 400, 500, 503, etc.) ──────────────
        $exceptions->render(function (HttpException $e, Request $request) {
            $code = $e->getStatusCode();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage() ?: 'An HTTP error occurred.',
                    'status'  => $code,
                ], $code);
            }

            $view = "errors.{$code}";
            if (view()->exists($view)) {
                return response()->view($view, ['exception' => $e], $code);
            }

            // Fallback for uncovered codes
            return response()->view('errors.minimal', [
                'code'    => $code,
                'title'   => 'Error ' . $code,
                'message' => $e->getMessage() ?: 'An unexpected error occurred.',
            ], $code);
        });

        // ── Catch-all: unexpected server errors ───────────────────────────────
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->expectsJson()) {
                $debug = config('app.debug');
                return response()->json(array_filter([
                    'message'   => $debug ? $e->getMessage() : 'An unexpected error occurred. Please try again.',
                    'exception' => $debug ? get_class($e) : null,
                    'file'      => $debug ? $e->getFile() : null,
                    'line'      => $debug ? $e->getLine() : null,
                ]), 500);
            }

            if (app()->hasDebugModeEnabled()) {
                return null; // Let Ignition/Whoops handle it in dev
            }

            return response()->view('errors.500', ['exception' => $e], 500);
        });

    })->create();
