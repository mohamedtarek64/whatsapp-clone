<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatApiController;
use App\Http\Controllers\ContactApiController;
use App\Http\Controllers\MessageApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Rutas para la gestión de Usuarios
// Se definen las rutas que no estarán protegidas por Sanctum, ya que no son requeridas.
// En esta instancia recien se obtendrá el API TOKEN para poder acceder a las rutas protegidas por Sanctum.
Route::post('auth/register',[AuthController::class, 'create']);
Route::post('auth/login',[AuthController::class, 'login']);

// Se utiliza el "middleware" de Sanctum para proteger las rutas.
// Se necesitará el API TOKEN para poder acceder a las rutas protegidas por el middleware de Sanctum.
Route::middleware(['auth:sanctum'])->group(function(){

    // Rutas para la gestión de Contactos
    // La función Route::resource es un método que crea automáticamente las rutas comunes
    // para realizar operaciones CRUD (Create, Read, Update, Delete) en el recurso especificado.
    Route::resource('contacts', ContactApiController::class);

    // Rutas para las acciones del chat.
    // Obtiene todos los chats del usuario autenticado.
    // Al tener el método __invoke en la classe ChatApiController, podemos hacer uso de esta sintaxis, ya que siempre se va a ejecutar el método __invoke
    Route::get('/chats', ChatApiController::class);

    // Rutas para las acciones de los mensajes del chat.
    // Obtiene todos los mensajes de un chat específico.
    Route::get('/chats/{chat}/messages', [MessageApiController::class, 'index']);
    // Enviar un nuevo mensaje a un chat específico.
    Route::post('/chats/{chat}/messages', [MessageApiController::class, 'store']);
    // Actualiza todos los mensajes sin leer de un chat especifico a leídos.
    Route::patch('/chats/{chat}/messages/read', [MessageApiController::class, 'markAsRead']);
    // Obtiene todos los mensajes no leídos de un chat específico.
    Route::get('/chats/{chat}/messages/unread', [MessageApiController::class, 'unreadMessages']);
});

