<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Http\Request;
use App\Rules\InvalidEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ContactApiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $contacts = auth()->user()->contacts()->paginate();
        return response()->json($contacts);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validamos los datos enviados por HTTP desde el formulario
        $request->validate([
            'name' => 'required', // Obligatorio
            'email' => [
                'required',       // Obligatorio
                'email',          // Debe tener formato de correo electrónico
                'exists:users',   // Debe existir en la tabla de usuarios de la base de datos
                Rule::notIn([auth()->user()->email]), // El correo electrónico no puede ser igual al correo electrónico del usuario autenticado en la aplicación
                new InvalidEmail  // Regla Personalizada en el archivo: "app\Rules\InvalidEmail.php". Esta Regla valida que el email ingresado no pertenezca ya a un Contacto
            ]
        ]);
        /* =================================================================================================================================================================== */
        /* LOGICA QUE AGREGA UN USUARIO COMO CONTACTO */

        // Se busca el usuario en la BD con el correo electronico enviado desde el formulario
        $user = User::where('email', $request->email)->first();

        // Hace un INSERT en la tabla "Contact" con los datos obtenidos de la tabla "User" filtrado por el Correo Electrónico

        /*
            El SQL Equivalente sería:

            INSERT INTO contacts (name, user_id, contact_id)
            VALUES (<$request->name>, <auth()->id()>, <$user->id>)
        */

        $contact = Contact::create([
            'name' => $request->name,
            'user_id' => auth()->id(),
            'contact_id' => $user->id
        ]);

        /* =================================================================================================================================================================== */
        /*LOGICA QUE CREA EL CHAT ENTRE EL USUARIO AUTENTICADO Y EL CONTACTO AGREGADO */

        // Esta query obtiene el chat individual entre los dos usuarios, si existe...
        $chatIndividual = DB::table('chat_user as u')
        ->join('chat_user as c', 'u.chat_id', '=', 'c.chat_id')
        ->select('u.*')
        ->where('u.user_id', auth()->id())
        ->where('c.user_id', $user->id)
        ->where(function ($query) {
            // Agregamos una subconsulta para contar los usuarios en el chat
            $query->whereExists(function ($subquery) {
                $subquery->select(DB::raw(1))
                    ->from('chat_user as uc')
                    ->whereRaw('uc.chat_id = u.chat_id')
                    ->havingRaw('COUNT(uc.user_id) = 2'); // Verifica que haya exactamente 2 usuarios en el chat
            });
        })
        ->get();

        // Validamos si ya existe un chat individual entre los usuarios
        if(count($chatIndividual) == 0){
            // Si no existe creado un chat individual entre los usuarios, se crea uno.

            // SQL Equivalente:
            // INSERT INTO chats (id, created_at, updated_at) VALUES (:id, :created_at, :updated_at)
            $newChat = Chat::create();

            /*
                El método attach en Laravel es utilizado para agregar registros a una tabla pivote de una relación de muchos a muchos.
                En este caso, $this->chat->users() es una instancia del constructor de consultas de Laravel para la relación users en el modelo Chat.
                El método attach toma una matriz de ID de usuarios y agrega una fila para cada uno de ellos en la tabla pivote, estableciendo la relación
                entre el chat y el usuario especificado.

                Por ejemplo, si $this->chat representa un chat con ID 10 y [auth()->user()->id, $this->contactChat->contact_id] es una matriz de dos ID de usuarios,
                attach agregaría las siguientes filas a la tabla pivote:

                    user_id | chat_id
                    --------+--------
                    1     |   10
                    2     |   10

                Esto establecería una relación entre el chat con ID 10 y los usuarios con ID 1 y 2.
            */

            // SQL Equivalente:
            // INSERT INTO chat_user (user_id, chat_id) VALUES (:user_id, :chat_id)
            $newChat->users()->attach([auth()->user()->id, $contact->contact_id]);
        }



        /* =================================================================================================================================================================== */
        /* RESPUESTA */

        return response()->json([
            'status' => true,
            'message' => 'Contacto agregado exitosamente',
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Contact $contact)
    {
        return response()->json([
            'status' => true,
            'data' => $contact
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Contact $contact)
    {
        // Validamos los datos enviados por HTTP desde el formulario
        $request->validate([
            'name' => 'required', // Obligatorio
            'email' => [
                'required',       // Obligatorio
                'email',          // Debe tener formato de correo electrónico
                'exists:users',   // Debe existir en la tabla de usuarios de la base de datos
                Rule::notIn([auth()->user()->email]), // El correo electrónico no puede ser igual al correo electrónico del usuario autenticado en la aplicación
                new InvalidEmail($contact->user->email)  // Regla Personalizada en el archivo: "app\Rules\InvalidEmail.php". Esta Regla valida que el email ingresado no pertenezca ya a un Contacto
            ]
        ]);

        // Se busca el usuario en la BD con el correo electronico enviado desde el formulario
        $user = User::where('email', $request->email)->first();

        // Hace un UPDATE en la tabla "Contacts" filtrado por el ID de Contacto
        /*
            El SQL Equivalente sería:

            UPDATE contacts
            SET name = [$request->name],
                contact_id = [$user->id]
            WHERE id = [$contact->id]
        */
        $contact->update([
            'name' => $request->name,
            'contact_id' => $user->id
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Contacto actualizado exitosamente',
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Contact $contact)
    {
        // Hace un DELETE en la tabla "Contacts" filtrado por el ID de Contacto

        /*
            El SQL Equivalente sería:

            DELETE FROM contacts
            WHERE id = $contact->id
        */
        $contact->delete();

        return response()->json([
            'status' => true,
            'message' => 'Contacto eliminado exitosamente',
        ], 200);
    }
}
