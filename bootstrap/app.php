<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'permission' => \App\Http\Middleware\CheckPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Custom error pages using Inertia
        $exceptions->respond(function (\Symfony\Component\HttpFoundation\Response $response, \Throwable $exception, \Illuminate\Http\Request $request) {
            if (!app()->environment(['local', 'testing']) && in_array($response->getStatusCode(), [500, 503, 404, 403])) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => $exception->getMessage() ?: 'Terjadi kesalahan pada server.'
                    ], $response->getStatusCode());
                }

                if ($response->getStatusCode() === 404) {
                    return \Inertia\Inertia::render('Errors/404', [
                        'status' => 404
                    ])->toResponse($request)->setStatusCode(404);
                }

                if ($response->getStatusCode() === 403) {
                    return \Inertia\Inertia::render('Errors/403', [
                        'status' => 403,
                        'message' => $exception->getMessage() ?: 'Anda tidak memiliki akses.'
                    ])->toResponse($request)->setStatusCode(403);
                }

                if (in_array($response->getStatusCode(), [500, 503])) {
                    return \Inertia\Inertia::render('Errors/500', [
                        'status' => $response->getStatusCode()
                    ])->toResponse($request)->setStatusCode($response->getStatusCode());
                }
            }

            return $response;
        });
    })->create();
