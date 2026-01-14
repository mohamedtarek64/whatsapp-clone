<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\User;
use App\Rules\InvalidEmail;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Se recupera todos los registros de la tabla "Contact", lo pagina y se envía el array a la vista.
        $contacts = auth()->user()->contacts()->paginate();
        return view('contacts.index', compact('contacts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view('contacts.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    // Almacena un nuevo contacto en la base de datos.
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

        // flash.banner => Es una variable recervada por Jetstream para mostrar el mensaje en un componente
        session()->flash('flash.banner', 'El contacto se ha creado correctamente');

        // flash.bannerStyle => Es una variable recervada por Jetstream para dar estilo al componente que mostrará el mensaje
        // Existen dos opciones de estilos "success" y "danger"
        session()->flash('flash.bannerStyle', 'success');

        // Redirecciona al apartado de edición de contactos
        return redirect()->route('contacts.edit', $contact);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $contact => Recibe como parametro un ID y lo relaciona automaticamente con el modelo Contact y de este modo se obtiene todo el registro relacionado con este ID en la tabla Contact
     * @return \Illuminate\Http\Response
     */
    public function edit(Contact $contact)
    {
        //
        return view('contacts.edit', compact('contact'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $contact => Recibe como parametro un ID y lo relaciona automaticamente con el modelo Contact y de este modo se obtiene todo el registro relacionado con este ID en la tabla Contact
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

        // flash.banner => Es una variable recervada por Jetstream para mostrar el mensaje en un componente
        session()->flash('flash.banner', 'El contacto se actualizó correctamente');

        // flash.bannerStyle => Es una variable recervada por Jetstream para dar estilo al componente que mostrará el mensaje
        // Existen dos opciones de estilos "success" y "danger"
        session()->flash('flash.bannerStyle', 'success');

        // Redirecciona al apartado de edición de contactos
        return redirect()->route('contacts.edit', $contact);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $contact => Recibe como parametro un ID y lo relaciona automaticamente con el modelo Contact y de este modo se obtiene todo el registro relacionado con este ID en la tabla Contact
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

        // flash.banner => Es una variable recervada por Jetstream para mostrar el mensaje en un componente
        session()->flash('flash.banner', 'El contacto se eliminó correctamente');

        // flash.bannerStyle => Es una variable recervada por Jetstream para dar estilo al componente que mostrará el mensaje
        // Existen dos opciones de estilos "success" y "danger"
        session()->flash('flash.bannerStyle', 'success');

        // Redirecciona al apartado de edición de contactos
        return redirect()->route('contacts.index',);
    }
}
