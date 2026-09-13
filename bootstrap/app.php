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
    ->withMiddleware(function (Middleware $middleware) {
        // [FIX] Karena rute login sekarang bernama 'admin.login' (bukan default
        // 'login'), Laravel perlu diberi tahu ke mana harus redirect saat ada
        // yang belum login mencoba akses halaman ber-middleware 'auth'.
        // Tanpa ini, akan muncul error "Route [login] not defined".
        $middleware->redirectGuestsTo(fn () => route('admin.login'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();