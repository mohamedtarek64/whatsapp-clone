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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('user_id')->constrained();
            // ---------------------------------------------------------------
            // Creamos una 2da relación con la tabla "users" pero relacionandolo con el campo "contact_id" que contendrá el "user_id" de la tabla "users"
            // Pero como ya ocupamos la referencia "user_id" para almacenar el ID del Propietario, se tuvo que realizar la siguiente relación
            // para almacenar los ID de los Usuarios que serán el Contacto del Propietario de la Cuenta.
            $table->unsignedBigInteger('contact_id');
            $table->foreign('contact_id')->references('id')->on('users');
            // ---------------------------------------------------------------
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
        Schema::dropIfExists('contacts');
    }
};
