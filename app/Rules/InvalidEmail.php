<?php

namespace App\Rules;

use App\Models\Contact;
use Illuminate\Contracts\Validation\Rule;

class InvalidEmail implements Rule
{

    public $email;

    /**
     * Crear una nueva instancia de regla.
     *
     * @return void
     */
    public function __construct($email = null)
    {
        // Se define este atributo para validar que se permitan una actualización con el email ya asignado al contacto
        $this->email = $email;
    }

    /**
     * Determinar si la regla de validación pasa.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // Validamos si el "email" ya lo tiene otro Contacto, si ya lo tiene se retorna "false", sino "true"
        /*
            where() => se utiliza para especificar las condiciones que deben cumplirse para que un resultado sea incluido en la consulta.
                        En este caso, se está buscando un contacto que tenga una propiedad user_id igual al id del usuario autenticado.

            whereHas() => se utiliza para especificar una condición adicional en la consulta basada en la relación de un modelo con otro.
                            En este caso, se está verificando si el contacto encontrado tiene un usuario asociado que tenga un correo electrónico específico. Esta función acepta una función de callback como argumento, que a su vez acepta una consulta como argumento. La función de callback es utilizada para agregar una condición adicional a la consulta original, en este caso verificando si el correo electrónico del usuario asociado coincide con el valor especificado.

            count() => se utiliza para contar cuántos resultados cumplen con las condiciones especificadas en la consulta.
                        Si el resultado de esta función es igual a cero, entonces se devuelve true, lo que indica que no hay ningún contacto que cumpla con
                        las condiciones especificadas en la consulta. De lo contrario, se devuelve false, lo que indica que sí hay al menos un contacto que
                        cumple con las condiciones.
        */

        /*
            El SQL Equivalente sería:

            SELECT COUNT(*)
                FROM contacts
                WHERE user_id = [auth()->id()]
                    AND EXISTS (
                            SELECT *
                            FROM users
                            WHERE users.id = contacts.user_id
                            AND  email = [$value]
                            AND  ( email != [$this->email] OR email IS NULL )
                        )
        */
        return Contact::where('user_id', auth()->id()) // En la tabla "Contacts" se filtra por el campo "user_id"
                        ->whereHas('user', function($query) use ($value){ // En una subquery se hace referencia a la tabla "user"
                            $query->where('email', $value) // Se filtra por el campo "email" de la tabla "user"
                                ->when($this->email, function($query){ // Se agrega la condición que solo se ejecute la query cuando el parametro "$this->email" sea distinto de NULL
                                    $query->where('email', '!=', $this->email); // Se filtra por el campo "email" en la tabla "user" y se le indica que obtenga los registros distinto al parametro "$this->email"
                                });
                        })->count() === 0;
    }

    /**
     * Obtener el mensaje de error de validación.
     *
     * @return string
     */

    // Aqui irá el mensaje de validación en el casó que no se cumpla la condición del método "passes"
    public function message()
    {
        return 'El correo electronico ingresado, ya se encuentra registrado.';
    }
}
