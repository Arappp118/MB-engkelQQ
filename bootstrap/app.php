<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
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
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role'      => \App\Http\Middleware\RoleMiddleware::class,
            'ownership' => \App\Http\Middleware\CheckOwnership::class,
        ]);

        $middleware->web(append: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Format error konsisten untuk API, tidak pernah expose stack trace
        // atau pesan exception internal — apapun nilai APP_DEBUG.
        $exceptions->render(function (\Throwable $e, Request $request) {
            if (!$request->is('api/*')) {
                return null;
            }

            $status = 500;
            $message = 'Terjadi kesalahan pada server.';
            $errors = null;

            if ($e instanceof ValidationException) {
                $status = 422;
                $message = 'Data yang dikirim tidak valid.';
                $errors = $e->errors();
            } elseif ($e instanceof AuthenticationException) {
                $status = 401;
                $message = 'Anda harus login terlebih dahulu.';
            } elseif ($e instanceof AuthorizationException) {
                $status = 403;
                $message = 'Anda tidak memiliki akses.';
            } elseif ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
                $status = 404;
                $message = 'Data tidak ditemukan.';
            } elseif ($e instanceof MethodNotAllowedHttpException) {
                $status = 405;
                $message = 'Metode tidak diperbolehkan.';
            } elseif ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                // Preserve status code asli (mis. 429 Too Many Requests),
                // tapi tetap tidak expose pesan exception internal.
                $status = $e->getStatusCode();
                $message = $status === 429
                    ? 'Terlalu banyak percobaan, silakan coba lagi nanti.'
                    : 'Terjadi kesalahan pada permintaan.';
            }

            return response()->json([
                'success' => false,
                'message' => $message,
                'errors'  => $errors,
            ], $status);
        });
    })->create();
