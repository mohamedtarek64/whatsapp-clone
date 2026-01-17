<?php

use App\Http\Controllers\ContactController;
use App\Http\Livewire\ChatComponent;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Route::resource() => establishes a set of predefined routes for a given resource.
//                      This allows automatically generating a full set of routes commonly used
//                      for CRUD operations (create, read, update, and delete) on a given resource.
// 1st Parameter => 1st part of the route which will then access the CRUD methods. Ex: contacts/show, contacts/create, etc.
// 2nd Parameter => Controller must have the 7 CRUD methods mandatorily or else it will throw an error.
//                  The 7 CRUD methods are: index, create, store, show, edit, update, destroy

// ->names('contacts') => Assigns the name "contacts" in plural to the routes, meaning they will start with the word "contacts".
//                        For example: contacts.index, contacts.show, etc.

// ->except('show') => We indicate to include all methods of the "ContactController" class except the "show" method (Because we are not using it).
//                      This method is useful when there are methods we won't use, so they don't need to be in the controller.
// 1st Parameter => The name of the method that will not be included.
Route::middleware('auth')->resource('contacts', ContactController::class)->names('contacts')->except('show');

// Route that will manage the "Chat" Component Controller created with Livewire
Route::get('/chat', ChatComponent::class)
    ->middleware('auth')
    ->name('chat.index');

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');
