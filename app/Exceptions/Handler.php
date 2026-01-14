<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        //  el método "renderable" se utiliza para personalizar el manejo de la excepción NotFoundHttpException.
        $this->renderable(function (NotFoundHttpException $e, $request) {
            // Se verifica si la solicitud coincide con ciertas rutas relacionadas.
            // Si la solicitud coincide con alguna de estas rutas, entonces se envía una respuesta JSON con un código
            // de estado HTTP 404 (Not Found) y un mensaje de error específico

            // Excepción para los departamentos no encontrados.
            if($request->is('api/contacts/*')){
                return response()->json([
                    'status' => false,
                    'message' => 'Contacto invalido'
                ], 404);
            }
        });
    }
}
