<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'last_seen_at',
        'chat_wallpaper',
        'dark_mode',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url',
    ];

    public function contacts()
    {
        // hasMany() => Método que hace la relación de "Uno a Muchos"
        //  -> Parametro => Modelo con el que se quiere relacionar
        return $this->hasMany(Contact::class);
    }

    public function messages(){
        // hasMany() => Método que hace la relación de "Uno a Muchos"
        //  -> Parametro => Modelo con el que se quiere relacionar
        return $this->hasMany(Message::class);
    }

    // Relación basada en una tabla pivot llamada "chat_user"
    public function chats()
    {
        // belongsToMany() => Método que hace la relación de "Muchos a Muchos"
        //->withPivot() => Como la relación se basa en una tabla pivot,
        //                 se debe especificar con el método "withPivot()" aquellos campos que se quieren obtener
        //                 (Ya que si no se especifica estos, la consulta solo devolverá los ID's que se relacionan)
        // ->withTimestamps() => Almacena la hora y fecha de la creación y actualización del registro.
        return $this->belongsToMany(Chat::class)
                    ->withPivot('color', 'active', 'is_pinned', 'muted_until', 'is_archived', 'is_admin')
                    ->withTimestamps();
    }

    public function stories()
    {
        return $this->hasMany(Story::class);
    }

    public function activeStories()
    {
        return $this->hasMany(Story::class)->active();
    }
}
