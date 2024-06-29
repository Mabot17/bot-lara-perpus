<?php

use App\Traits\ResponseApiTrait;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Http\Request; // Tambahkan ini untuk mengimpor kelas Request

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {


        // Hanlde global response
        $exceptions->render(function (MethodNotAllowedHttpException $e, Request $request) {

            if ($request->is('api/*')) {
                // Mendapatkan nilai header 'Accept' dari permintaan
                $acceptHeader = request()->header('Accept');

                // Mengecek apakah nilai header 'Accept' adalah 'application/json' atau '*/*'
                if ($acceptHeader !== 'application/json') {
                    // Jika tidak, Anda dapat menangani kasus tersebut di sini
                    return ResponseApiTrait::showErrorHeader();
                }

                $allowedMethods = $e->getHeaders()['Allow']; // Mendapatkan metode yang diizinkan dari header exception
                return ResponseApiTrait::showMethodNotAllowed($allowedMethods);
            }
        });

    })->create();
