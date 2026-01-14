<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    // Atributos protegidos por laravel, donde este espera que el usuario haga el INSERT.
    // Cada vez que se creá un campo nuevo en la tabla "contact" se debe agregar en este array.
    // De lo contrario, se obtendrá el mensaje:
    // SQLSTATE[HY000]: General error: 1364 Field '[NOMBRE_CAMPO]' doesn't have a default value
    protected $fillable = [
        'name', 'user_id', 'contact_id'
    ];

    public function user()
    {
        // belongsTo() => Método que hace la relación de "Muchos a Uno"
        //  -> 1er Parametro => Modelo con el que se quiere relacionar
        //  -> 2do Parametro => Foreing Key al que se quiere hacer referencia.
        //                      En este caso si no se pasa "contact_id" tomará por defecto el Foreing Key "user_id"
        return $this->belongsTo(User::class, 'contact_id');
    }
}
