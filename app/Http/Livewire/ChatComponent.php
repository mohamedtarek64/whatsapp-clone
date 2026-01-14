<?php

namespace App\Http\Livewire;

use App\Models\Chat;
use App\Models\Contact;
use App\Models\Message;
use Livewire\Component;

// Importamos la Libreria de Notificaciones
use Illuminate\Support\Facades\Notification;

class ChatComponent extends Component
{

    public $search;
    public $contactChat;
    public $chat;
    public $chat_id;
    public $bodyMessage;
    public $users;

    // Método que se ejecuta antes de inicializarce el componente de livewire "ChatComponent"
    // Inicializa la propiedad "users" como una colección/array vacia para evitar errores.
    public function mount(){
        $this->users = collect();
    }
    // =================================================================================================================================================
    /* OYENTES DE LIVEWIERE*/
    // Doc: https://laravel-livewire.com/docs/2.x/events#event-listeners

    // Este método se utiliza para escuchar en un canales especifico emitido por Pusher o Laravel Web Socket.
    // Para escuchar una lista de canales se debe retornar un array con los nombres de los canales y
    // los eventos que se van a ejecutar cuando se conecten a dicho canal.
    public function getListeners()
    {
        // Obtenemos el ID del usuario logedo
        $user_id = auth()->user()->id;

        // Retornamos un array donde:
        // 1er Parametro / Key del Array => Es el canal de Pusher o Laravel WebSocket por el cual se va a escuchar.
        //                                  (Se concatena la variable "$user_id" para hacerlo dinamico para cada usuario logeado)
        // 2do Parametro / Value del Array => Es el método que se va a ejecutar cada vez que se reciba una notificación a travez del
        //                                    canal especificado en el 1er Parametro/Key del array.

       // Mas detalles:
       // Sintaxis Especial: ['echo:{channel},{event}' => '{method}']

       // Tipo de Canales:
       // - Public: No requiere autenticación
       // - Presence: Requiere autenticación, y todos los usuarios autenticados pueden escuchar el canal (Especial para salas de chat)
       // - Private: Requiere autenticación, solo puede escuchar el canal los usuarios conectados, pero nadie conoce la información de nadie.

       // echo-notification => es un evento en tiempo real que se activa cuando se recibe una notificación.
       // App.Models.User.{$user_id} => especifica que esta escucha es para un usuario específico, se especifica como "App.Models.User"
       //                               y se concatena con el id del usuario autenticado.
       // notification => Indicamos a Livewire que el evento que ejecutará una transmisión será una notificación.
       //               Para agregar nuevos eventos se deben crear en el siguiente archivo "app\Providers\EventServiceProvider.php".
       // render => Es el método que se ejecutará luego de recibír una transmisión mediante el evento "notification"

       //Doc: https://laravel-livewire.com/docs/2.x/laravel-echo
        return[
            "echo-notification:App.Models.User.{$user_id},notification" => 'render',
            "echo-presence:chat.1,here" => 'chatHere',
            "echo-presence:chat.1,joining" => 'chatJoining',
            "echo-presence:chat.1,leaving" => 'chatLeaving'
        ];
    }
    // =================================================================================================================================================
    /*  PROPIEDAD COMPUTADAS: */
    /*
        Las propiedades computadas en Laravel son aquellas que no se almacenan en la base de datos, sino que se calculan dinámicamente a partir de otras
        propiedades de la clase. Es decir, son una forma de definir una propiedad que se "deriva" de otras propiedades, y que se puede utilizar como
        cualquier otra propiedad, pero sin necesidad de almacenarla en la base de datos.

        Por ejemplo, supongamos que tenemos una clase Product que tiene una propiedad price que almacena el precio del producto.
        Podríamos definir una propiedad computada llamada discountedPrice que se calcule restando un porcentaje de descuento al precio del producto:

        class Product
        {
            public function getDiscountedPriceProperty()
            {
                return $this->price * (1 - $this->discount);
            }
        }

        Luego, podríamos acceder a la propiedad discountedPrice de una instancia de la clase Product como si fuera una propiedad normal:

        $product = Product::find(1);
        echo $product->discountedPrice;


        TENER EN CUENTA: que para utilizar una propiedad computada se debe hacer uso de la siguiente convención.

                        public function get[NOMBRE_PROPIEDAD_COMPUTADA]Property(){}

                        Por ejemplo:

                        public function getEstoEsUnaPruebaProperty(){}

                        Y se deberá acceder de la siguiente forma:

                        $this->estoEsUnaPrueba;

    */

    // Propiedad computada que se utiliza en el buscador de contactos, en la vista podemos hacer uso de esta propiedad llamando a "$this->contacts"
    public function getContactsProperty()
    {

        // Obtiene los contactos filtrando por la propiedad "search" vinculado al input del buscador del chat
        /*
            El SQL equivalente de la siguiente consulta con el ORM de Laravel es:

            SELECT *
            FROM contacts
            WHERE user_id = {auth()->id()}
            AND (
                name LIKE '%{$this->search}%'
                OR EXISTS (
                    SELECT *
                    FROM users
                    WHERE users.id = contacts.user_id
                    AND email LIKE '%{$this->search}%'
                )
            )

            Si la consulta no obtiene registros se retorna un Array vacio para evitar error con respecto a la directiva "@forelse" del frontend
        */
        return Contact::where('user_id', auth()->id())
                ->when($this->search, function($query){
                    $query->where(function($query){
                        $query->where('name', 'like', '%'.$this->search.'%')
                            ->orWhereHas('user', function($query){
                                $query->where('email', 'like', '%'.$this->search.'%');
                            });
                    });
                })
                ->get() ?? [];
    }

    // Propiedad computada que se utiliza al seleccionar un contacto y mostrar los mensajes existentes en el chat. , en la vista podemos hacer uso de esta propiedad llamando a "$this->messages"
    public function getMessagesProperty()
    {
        // Se obtiene el listado de mensajes del chat
        // Se utiliza $this->chat->messages()->get() en vez de $this->chat->messages, por que si utilizamos solo $this->chat->messages, este nos mostrará una instancia del chat
        // y si usamos $this->chat->messages()->get(), ejecutará nuevamente la consulta y obtendrá la una nueva instancia del chat todo el tiempo.

        // $this->chat->messages()->get() es lo mismo que utilizar Messages::where('chat_id', $this->chat->id)->get()

        return $this->chat ? $this->chat->messages()->get() : [];
    }

    // Propiedad computada que se utiliza para obtener los chats ordenados de manera Descendente por el Mutador "last_message_at", es decir,
    // los mensajes con la fecha del mensaje mas reciente se mostrarán primeros en la lista
    public function getChatsProperty()
    {
        return auth()->user()->chats()->get()->sortByDesc('last_message_at');
    }

    // Propiedad computada que se utiliza para obtener el/los ID/s Usuario de/los Contacto/s el cual enviamos un mensaje
    public function getUsersNotificationsProperty()
    {
        return $this->chat ? $this->chat->users->where('id', '!=', auth()->id()) : [];
    }

    // Compara los usuarios que se encuentan activos (Los IDs de los usuarios que se encuentra dentro de $this->users)
    // Con el usuario que se encuentra en el chat al que se ingresó.
    public function getActiveProperty(){
        // ->contains() =>  Es un método de colección que determina si un determinado elemento está presente en la colección o no
        return $this->users->contains($this->users_notifications->first()->id);
    }
    // =================================================================================================================================================
    /* CICLO DE VIDA */

    // updatedBodyMessage => Utilizando la convención "update" luego el nombre de la propiedad $bodyMessage lo que hace es escuchar cuando se modifique el valor de este
    //                       y obtener el nuevo valor por el atributo "$value".
    // Para agregar un evento que escuche todo el tiempo si se modifica el valor de una propiedad lo que se debe hacer es:
    //  1) Crear la propiedad en la clase
    //  2) crear un método siguiente la siguiente convención:
    //          update[NOMBRE_PROPIEDAD]
    //  Ej:
    //          updatedBodyMessage
    //
    // Tener en cuenta que el nombre de la propiedad se empieza con mayusculas
    public function updatedBodyMessage($value)
    {
        if($value){
            // Utilizamos el Facade "Notification" para enviar la notificación a Pusher
            // 1er parametro -> ID de los usuarios a notificar
            // 2do Parametro -> Ruta y Nombre de la Clase de la notificación creada
            Notification::send($this->getUsersNotificationsProperty(), new \App\Notifications\UserTyping($this->chat->id));
        }
    }

    // =================================================================================================================================================
    /* FUNCIONES QUE SE EJECUTAN POR UN EVENTO */

    // Obtiene o Crea el Chat del Contacto que se seleccionó
    public function open_chat_contact(Contact $contact)
    {

        // Obtiene el chat en el que el usuario actual tiene una conversación con el contacto especificado
        /*SQL Equivalente:

            SELECT * FROM chats
                INNER JOIN chat_user ON chat_user.chat_id = chats.id
                WHERE chat_user.user_id = :current_user_id
                AND EXISTS (SELECT *
                            FROM chat_user
                            WHERE chat_user.chat_id = chats.id
                            AND chat_user.user_id = :contact_id)
                AND (SELECT COUNT(*)
                        FROM chat_user
                        WHERE chat_user.chat_id = chats.id) = 2
                LIMIT 1
        */
        $chat = auth()->user()->chats()
            ->whereHas('users', function($query) use ($contact){
                // Filtra los chats que tienen al usuario especificado como participante
                $query->where('user_id', $contact->contact_id);
            })
            // Solo selecciona chats que tienen exactamente dos participantes
            ->has('users', 2)
            // Obtiene el primer chat que cumpla con los criterios
            ->first();

        // Si se encontró un chat existente, lo asigna a la propiedad de la clase
        if($chat){
            $this->chat = $chat;
            // Se almacena el id del chat abierto a la propiedad "chat_id" para utilizarlo en la vista
            // mediante la directiva "@entangle('chat_id')"
            $this->chat_id = $chat->id;
            // Resetea el campo contactChat para mostrar la imagen y el nombre de la propiedad chat
            $this->reset('bodyMessage','contactChat', 'search');
        }else{
            // Si no se encontró un chat existente, asigna el contacto a la propiedad de la clase
            $this->contactChat = $contact;

            // Resetea el campo chat para mostrar la imagen y el nombre de la propiedad contactChat
            $this->reset('bodyMessage','chat', 'search');
        }
    }

    public function open_chat(Chat $chat)
    {
        $this->chat = $chat;
        // Se almacena el id del chat abierto a la propiedad "chat_id" para utilizarlo en la vista
        // mediante la directiva "@entangle('chat_id')"
        $this->chat_id = $chat->id;
        $this->reset('contactChat', 'bodyMessage');

        // Actualizamos el campo "is_read" a "true" en la base de datos
        $chat->messages()->where('user_id', '!=', auth()->id())->where('is_read', false)->update([
            'is_read' => true
        ]);

        // Utilizamos el Facade "Notification" para enviar la notificación a Pusher
        // 1er parametro -> ID de los usuarios a notificar
        // 2do Parametro -> Ruta y Nombre de la Clase de la notificación creada (En este caso se llama "ReadMessage")
        Notification::send($this->getUsersNotificationsProperty(), new \App\Notifications\ReadMessage);
    }

    public function sendMessage()
    {
        // Valida que el cuerpo del mensaje no esté vacío
        $this->validate([
            'bodyMessage' => 'required'
        ]);

        // Si no hay una conversación existente, crea una nueva y agrega a los usuarios actuales
        if(!$this->chat){
            // SQL Equivalente:
            // INSERT INTO chats (id, created_at, updated_at) VALUES (:id, :created_at, :updated_at)
            $this->chat = Chat::create();
            // Se almacena el id del chat abierto a la propiedad "chat_id" para utilizarlo en la vista
            // mediante la directiva "@entangle('chat_id')"
            $this->chat_id = $this->chat->id;

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
            $this->chat->users()->attach([auth()->user()->id, $this->contactChat->contact_id]);
        }

        // Crea un nuevo mensaje en la conversación actual y asigna el autor como el usuario actual
        // SQL Equivalente:
        // INSERT INTO messages (id, body, user_id, chat_id, created_at, updated_at) VALUES (:id, :body, :user_id, :chat_id, :created_at, :updated_at)
        $this->chat->messages()->create([
            'body' => $this->bodyMessage,
            'user_id' => auth()->user()->id
        ]);


        // Utilizamos el Facade "Notification" para enviar la notificación a Pusher
        // 1er parametro -> ID de los usuarios a notificar
        // 2do Parametro -> Ruta y Nombre de la Clase de la notificación creada (En este caso se llama "NewMessage")
        Notification::send($this->getUsersNotificationsProperty(), new \App\Notifications\NewMessage);

        // Resetea los campos de mensaje y contacto para una nueva entrada
        $this->reset('bodyMessage', 'contactChat');
    }

    // Función que se ejecuta en el momento que nos encontremos ubicados en la página chat
    // $users => Obtendrá los datos del usuario retornado en el archivo "routes\channels.php" del Broadcast 'chat.{id}'
    // en el canal con el nombre "chat.1" de tipo "presence"
    public function chatHere($users)
    {
        $this->users = collect($users)->pluck('id');
    }

    // Función que se ejecuta en el momento que ingresa un nuevo usuario a la página chat
    // $users => Obtendrá los datos del usuario retornado en el archivo "routes\channels.php" del Broadcast 'chat.{id}'
    // en el canal con el nombre "chat.1" de tipo "presence"
    public function chatJoining($user)
    {
        // Si un usuario ingresó a la sala de chats se ingresa el ID de este en la propiedad "$this->users"
        $this->users->push($user['id']);
    }

    // Función que se ejecuta en el momento que un usuario sale de la página chat
    // $users => Obtendrá los datos del usuario retornado en el archivo "routes\channels.php" del Broadcast 'chat.{id}'
    // en el canal con el nombre "chat.1" de tipo "presence"
    public function chatLeaving($user)
    {
        // Si un usuario salió de la sala de chats se quita el ID de este de la propiedad "this->users"
        $this->users = $this->users->filter(function($id) use ($user){
            // Recorre la propiedad "$this->users" (es una colección) y retorna un nuevo array con el ID distinto al que se le pasó como parametro
            return $id != $user['id'];
        });
    }

    // Método que se ejecuta para renderizar el componente de Livewire sin recargar la página
    public function render()
    {
        if($this->chat){

            // Actualizamos el campo "is_read" a "true" en la base de datos
            $this->chat->messages()->where('user_id', '!=', auth()->id())->where('is_read', false)->update([
                'is_read' => true
            ]);

            // Utilizamos el Facade "Notification" para enviar la notificación a Pusher
            // 1er parametro -> ID de los usuarios a notificar
            // 2do Parametro -> Ruta y Nombre de la Clase de la notificación creada (En este caso se llama "ReadMessage")
            // Notification::send($this->getUsersNotificationsProperty(), new \App\Notifications\ReadMessage);

            // Desencadena un evento personalizado en Livewire.
            // Esto significa que cuando se ejecuta esta línea de código, Livewire detecta el evento "scrollIntoView" y
            // ejecuta todas las funciones de escucha asociadas a ese evento.
            $this->emit('scrollIntoView');
        }


        // view('livewire.chat-component') => Muestra la vista del componente "chat" de Livewire
        // ->layout('[Ruta Layout]') => Se utiliza para asignar un Layouts a un componente de Livewire.
        //      Parametro => Recibe como parametro la ruta del layout que se quiere utilizar, en este caso se utilizó "layouts.chat"
        return view('livewire.chat-component')->layout('layouts.chat');
    }
}
