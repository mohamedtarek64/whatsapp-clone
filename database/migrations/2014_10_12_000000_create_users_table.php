<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable(); // fecha en la que el usuario verificó su dirección de correo electrónico. Esta columna puede tener un valor nulo si el usuario no ha verificado su correo electrónico.
            $table->string('password');
            $table->rememberToken(); // token utilizado por Laravel para recordar la sesión del usuario.
            $table->foreignId('current_team_id')->nullable(); // identificador de equipo asociado con el usuario. Puede tener un valor nulo si el usuario no está asociado con ningún equipo.
            $table->string('profile_photo_path', 2048)->nullable(); // ruta a la foto de perfil del usuario. Puede tener un valor nulo si el usuario no ha subido una foto de perfil.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
