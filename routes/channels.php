<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Aquí podrá registrar todos los canales de transmisión de eventos que soporta su aplicación.
| Las devoluciones de llamada de autorización de canal dadas se utilizan para verificar si un
| usuario autenticado puede escuchar el canal.
| Aquí se autorizan a los usuarios para los canales de tipo "Presence" y "Private".
|
*/


/*
| Esta función crea un nuevo canal de transmisión con el nombre: 'App.Models.User.{id}'.
| Luego con una función anonima recibe como parametro el ID del Usuario autenticado y el ID Usuario
| que se pasó en el nombre del Canal.
| Luego con estos parametros hace una camparación donde retorna un valor booleano si el
| Usuario autenticado es el mismo que creó el canal.
*/

// Canal de tipo "Private" => Este tipo de canal siempre espera que se retorne un valor booleano
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});


/*
| Esta función crea un nuevo canal de transmisión con el nombre: 'chat.{id}'.
| Luego con una función anonima recibe como parametro el ID del Usuario autenticado y el ID Usuario
| que se pasó en el nombre del Canal.
| Luego se retorna los datos del usuario que se conectó a este canal.
*/
// Canal de tipo "Presence" => Este tipo canal a diferencia del tipo "Private" se le puede retornar un valor como un objeto
Broadcast::channel('chat.{id}', function ($user, $id) {
    return $user;
});
