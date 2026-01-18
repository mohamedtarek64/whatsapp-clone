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

// Routes for User management
// Routes that will not be protected by Sanctum are defined, as they are not required.
// In this instance the API TOKEN will be obtained to access the routes protected by Sanctum.
Route::post('auth/register',[AuthController::class, 'create']);
Route::post('auth/login',[AuthController::class, 'login']);

// Sanctum "middleware" is used to protect routes.
// The API TOKEN will be needed to access the routes protected by the Sanctum middleware.
Route::middleware(['auth:sanctum'])->group(function(){

    // Routes for Contact management
    // The Route::resource function is a method that automatically creates common routes
    // to perform CRUD (Create, Read, Update, Delete) operations on the specified resource.
    Route::resource('contacts', ContactApiController::class);

    // Routes for chat actions.
    // Gets all chats of the authenticated user.
    // By having the __invoke method in the ChatApiController class, we can use this syntax, as the __invoke method will always be executed
    Route::get('/chats', ChatApiController::class);

    // Routes for chat message actions.
    // Gets all messages from a specific chat.
    Route::get('/chats/{chat}/messages', [MessageApiController::class, 'index']);
    // Send a new message to a specific chat.
    Route::post('/chats/{chat}/messages', [MessageApiController::class, 'store']);
    // Updates all unread messages of a specific chat to read.
    Route::patch('/chats/{chat}/messages/read', [MessageApiController::class, 'markAsRead']);
    // Gets all unread messages of a specific chat.
    Route::get('/chats/{chat}/messages/unread', [MessageApiController::class, 'unreadMessages']);
});

