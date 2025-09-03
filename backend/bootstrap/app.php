<?php

use App\Http\Middleware\TestMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'test' => TestMiddleware::class,
        ]);
        // $middleware->append(TestMiddleware::class); // prepend
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // 
    })->create();
