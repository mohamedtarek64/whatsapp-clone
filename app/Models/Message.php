<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    // Atributos protegidos por laravel, donde este espera que el usuario haga el INSERT.
    // Cada vez que se creá un campo nuevo en la tabla "messages" se debe agregar en este array.
    // De lo contrario, se obtendrá el mensaje:
    // SQLSTATE[HY000]: General error: 1364 Field '[NOMBRE_CAMPO]' doesn't have a default value
    protected $fillable = [
        'body', 'is_read', 'user_id', 'chat_id'
    ];

    public function chat(){
        // belongsTo() => Método que hace la relación de "Muchos a Uno"
        //  -> 1er Parametro => Modelo con el que se quiere relacionar
        return $this->belongsTo(Chat::class);
    }

    public function user(){
        // belongsTo() => Método que hace la relación de "Muchos a Uno"
        //  -> 1er Parametro => Modelo con el que se quiere relacionar
        return $this->belongsTo(User::class);
    }
}
