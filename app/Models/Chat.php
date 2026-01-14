<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Chat extends Model
{
    use HasFactory;

    // Atributos protegidos por laravel, donde este espera que el usuario haga el INSERT.
    // Cada vez que se creá un campo nuevo en la tabla "chat" se debe agregar en este array.
    // De lo contrario, se obtendrá el mensaje:
    // SQLSTATE[HY000]: General error: 1364 Field '[NOMBRE_CAMPO]' doesn't have a default value
    protected $fillable = [
        'name', 'image_url', 'is_group'
    ];

    // =================================================================================================================================================
    /* MUTADORES */

    public function name(): Attribute
    {
        return new Attribute(
            // Obtiene el nombre del chat o contacto
            get: function($value){
                // Si el chat es un grupo, devuelve el nombre del grupo
                if($this->is_group){
                    return $value;
                }
                //Sino
                // Obtiene el usuario con el que el usuario actual tiene una conversación
                $user = $this->users->where('id', '!=', auth()->id())->first();
                // Obtiene el contacto del usuario actual con el usuario encontrado
                $contact = auth()->user()->contacts()->where('contact_id', $user->id)->first();
                // Si se encontró un contacto, devuelve su nombre, de lo contrario, devuelve el correo electrónico del usuario
                return $contact ? $contact->name : $user->email;
            }
        );
    }

    public function image(): Attribute
    {
        return new Attribute(
            // Obtiene la imagen del chat o usuario
            get: function(){
                // Si el chat es un grupo, devuelve la URL de la imagen del grupo almacenada en el almacenamiento de Laravel
                if($this->is_group){
                    return Storage::url($this->image_url);
                }
                // Sino
                // Obtiene el usuario con el que el usuario actual tiene una conversación
                $user = $this->users->where('id', '!=', auth()->id())->first();
                // Devuelve la URL de la imagen de perfil del usuario
                return $user->profile_photo_url;
            }
        );
    }

    public function lastMessageAt(): Attribute
    {
        return new Attribute(
            get: function(){
                return $this->messages->last()->created_at;
            }
        );
    }

    // Obtiene la cantidad de mensajes de un chat que sean distinto al usuario autenticado y que el campo "is_read" sea igual a falso.
    // Es decir, obtiene los mensajes no leidos de un chat
    public function unreadMessages(): Attribute
    {
        return new Attribute(
            get: function(){
                return $this->messages()->where('user_id', '!=', auth()->id())->where('is_read', false)->count();
            }
        );
    }

    // =================================================================================================================================================

    public function messages()
    {
        // hasMany() => Método que hace la relación de "Uno a Muchos"
        //  -> Parametro => Modelo con el que se quiere relacionar
        return $this->hasMany(Message::class);
    }

    public function users()
    {
        // belongsToMany() => Método que hace la relación de "Muchos a Muchos"
        return $this->belongsToMany(User::class);
    }
}
