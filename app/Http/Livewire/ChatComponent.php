<?php

namespace App\Http\Livewire;

use App\Models\Chat;
use App\Models\Contact;
use App\Models\Message;
use Livewire\Component;

// We import the Notification Library
use Illuminate\Support\Facades\Notification;

class ChatComponent extends Component
{

    public $search;
    public $contactChat;
    public $chat;
    public $chat_id;
    public $bodyMessage;
    public $users;

    // Method executed before initializing the Livewire component "ChatComponent"
    // Initializes the "users" property as an empty collection/array to avoid errors.
    public function mount(){
        $this->users = collect();
    }
    // =================================================================================================================================================
    /* LIVEWIRE LISTENERS */
    // Doc: https://laravel-livewire.com/docs/2.x/events#event-listeners

    // This method is used to listen on specific channels emitted by Pusher or Laravel Web Socket.
    // To listen to a list of channels, an array must be returned with the channel names and
    // the events that will be executed when connecting to that channel.
    public function getListeners()
    {
        // We get the ID of the logged-in user
        $user_id = auth()->user()->id;

        // We return an array where:
        // 1st Parameter / Array Key => It is the Pusher or Laravel WebSocket channel to listen on.
        //                              (The "$user_id" variable is concatenated to make it dynamic for each logged-in user)
        // 2nd Parameter / Array Value => It is the method that will be executed each time a notification is received through the
        //                                channel specified in the 1st Parameter/Key of the array.

       // More details:
       // Special Syntax: ['echo:{channel},{event}' => '{method}']

       // Channel Types:
       // - Public: Does not require authentication
       // - Presence: Requires authentication, and all authenticated users can listen to the channel (Special for chat rooms)
       // - Private: Requires authentication, only connected users can listen to the channel, but no one knows anyone else's information.

       // echo-notification => is a real-time event triggered when a notification is received.
       // App.Models.User.{$user_id} => specifies that this listener is for a specific user, specified as "App.Models.User"
       //                               and concatenated with the authenticated user's id.
       // notification => We indicate to Livewire that the event triggering a broadcast will be a notification.
       //               To add new events, they must be created in the following file "app\Providers\EventServiceProvider.php".
       // render => It is the method that will be executed after receiving a broadcast through the "notification" event

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

    // Computed property used in the contact search; in the view, we can use this property by calling "$this->contacts"
    public function getContactsProperty()
    {
        // Gets contacts filtering by the "search" property linked to the chat search input
        /*
            The SQL equivalent of the following query with Laravel's ORM is:

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

            If the query returns no records, an empty Array is returned to avoid errors with the frontend "@forelse" directive
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

    // Computed property used when selecting a contact to show existing messages in the chat; in the view, we can use this property by calling "$this->messages"
    public function getMessagesProperty()
    {
        // Gets the list of messages in the chat
        // We use $this->chat->messages()->get() instead of $this->chat->messages, because if we only use $this->chat->messages, it will show an instance of the chat
        // and if we use $this->chat->messages()->get(), it will execute the query again and get a new instance of the chat every time.

        // $this->chat->messages()->get() is the same as using Messages::where('chat_id', $this->chat->id)->get()

        return $this->chat ? $this->chat->messages()->get() : [];
    }

    // Computed property used to get chats sorted Descending by the "last_message_at" Mutator, i.e.,
    // the messages with the most recent message date will be shown first in the list
    public function getChatsProperty()
    {
        return auth()->user()->chats()->get()->sortByDesc('last_message_at');
    }

    // Computed property used to get the User ID(s) of the Contact(s) to whom we send a message
    public function getUsersNotificationsProperty()
    {
        return $this->chat ? $this->chat->users->where('id', '!=', auth()->id()) : [];
    }

    // Compares users who are currently active (The IDs of users currently in $this->users)
    // With the user in the chat that was entered.
    public function getActiveProperty(){
        // ->contains() => It is a collection method that determines if a given element is present in the collection or not
        return $this->users->contains($this->users_notifications->first()->id);
    }
    // =================================================================================================================================================
    /* LIFE CYCLE */

    // updatedBodyMessage => Using the "updated" convention followed by the property name $bodyMessage listens for changes to its value
    //                       and gets the new value through the "$value" attribute.
    // To add an event that listens all the time if a property value is modified, you must:
    //  1) Create the property in the class
    //  2) create a method following this convention:
    //          updated[PROPERTY_NAME]
    //  Ex:
    //          updatedBodyMessage
    //
    // Keep in mind that the property name starts with a capital letter
    public function updatedBodyMessage($value)
    {
        if($value){
            // We use the "Notification" Facade to send the notification to Pusher
            // 1st parameter -> ID of the users to notify
            // 2nd Parameter -> Route and Name of the created notification class
            Notification::send($this->getUsersNotificationsProperty(), new \App\Notifications\UserTyping($this->chat->id));
        }
    }

    // =================================================================================================================================================
    /* FUNCTIONS EXECUTED BY AN EVENT */

    // Gets or Creates the Chat for the selected Contact
    public function open_chat_contact(Contact $contact)
    {

        // Gets the chat in which the current user has a conversation with the specified contact
        /*SQL Equivalent:

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
                // Filters chats that have the specified user as a participant
                $query->where('user_id', $contact->contact_id);
            })
            // Only selects chats that have exactly two participants
            ->has('users', 2)
            // Gets the first chat that meets the criteria
            ->first();

        // If an existing chat was found, it assigns it to the class property
        if($chat){
            $this->chat = $chat;
            // The ID of the open chat is stored in the "chat_id" property for use in the view
            // using the "@entangle('chat_id')" directive
            $this->chat_id = $chat->id;
            // Resets the contactChat field to show the image and name from the chat property
            $this->reset('bodyMessage','contactChat', 'search');
        }else{
            // If no existing chat was found, it assigns the contact to the class property
            $this->contactChat = $contact;

            // Resets the chat field to show the image and name from the contactChat property
            $this->reset('bodyMessage','chat', 'search');
        }
    }

    public function open_chat(Chat $chat)
    {
        $this->chat = $chat;
        // The ID of the open chat is stored in the "chat_id" property for use in the view
        // using the "@entangle('chat_id')" directive
        $this->chat_id = $chat->id;
        $this->reset('contactChat', 'bodyMessage');

        // We update the "is_read" field to "true" in the database
        $chat->messages()->where('user_id', '!=', auth()->id())->where('is_read', false)->update([
            'is_read' => true
        ]);

        // We use the "Notification" Facade to send the notification to Pusher
        // 1st parameter -> ID of the users to notify
        // 2nd Parameter -> Route and Name of the created notification class (In this case called "ReadMessage")
        Notification::send($this->getUsersNotificationsProperty(), new \App\Notifications\ReadMessage);
    }

    public function sendMessage()
    {
        // Validates that the message body is not empty
        $this->validate([
            'bodyMessage' => 'required'
        ]);

        // If there is no existing conversation, creates a new one and adds the current users
        if(!$this->chat){
            // SQL Equivalent:
            // INSERT INTO chats (id, created_at, updated_at) VALUES (:id, :created_at, :updated_at)
            $this->chat = Chat::create();
            // The ID of the open chat is stored in the "chat_id" property for use in the view
            // using the "@entangle('chat_id')" directive
            $this->chat_id = $this->chat->id;

            /*
                The attach method in Laravel is used to add records to a pivot table of a many-to- many relationship.
                In this case, $this->chat->users() is an instance of the Laravel query builder for the users relationship on the Chat model.
                The attach method takes an array of user IDs and adds a row for each of them in the pivot table, establishing the relationship
                between the chat and the specified user.

                For example, if $this->chat represents a chat with ID 10 and [auth()->user()->id, $this->contactChat->contact_id] is an array of two user IDs,
                attach would add the following rows to the pivot table:

                    user_id | chat_id
                    --------+--------
                    1     |   10
                    2     |   10

                This would establish a relationship between the chat with ID 10 and users with ID 1 and 2.
            */

            // SQL Equivalent:
            // INSERT INTO chat_user (user_id, chat_id) VALUES (:user_id, :chat_id)
            $this->chat->users()->attach([auth()->user()->id, $this->contactChat->contact_id]);
        }

        // Creates a new message in the current conversation and assigns the author as the current user
        // SQL Equivalent:
        // INSERT INTO messages (id, body, user_id, chat_id, created_at, updated_at) VALUES (:id, :body, :user_id, :chat_id, :created_at, :updated_at)
        $this->chat->messages()->create([
            'body' => $this->bodyMessage,
            'user_id' => auth()->user()->id
        ]);


        // We use the "Notification" Facade to send the notification to Pusher
        // 1st parameter -> ID of the users to notify
        // 2nd Parameter -> Route and Name of the created notification class (In this case called "NewMessage")
        Notification::send($this->getUsersNotificationsProperty(), new \App\Notifications\NewMessage);

        // Resets the message and contact fields for a new entry
        $this->reset('bodyMessage', 'contactChat');
    }

    // Function executed at the moment we are located on the chat page
    // $users => Will get the user data returned in the "routes\channels.php" file from the 'chat.{id}' Broadcast
    // in the channel named "chat.1" of type "presence"
    public function chatHere($users)
    {
        $this->users = collect($users)->pluck('id');
    }

    // Function executed at the moment a new user enters the chat page
    // $users => Will get the user data returned in the "routes\channels.php" file from the 'chat.{id}' Broadcast
    // in the channel named "chat.1" of type "presence"
    public function chatJoining($user)
    {
        // If a user entered the chat room, their ID is added to the "$this->users" property
        $this->users->push($user['id']);
    }

    // Function executed at the moment a user leaves the chat page
    // $users => Will get the user data returned in the "routes\channels.php" file from the 'chat.{id}' Broadcast
    // in the channel named "chat.1" of type "presence"
    public function chatLeaving($user)
    {
        // If a user left the chat room, their ID is removed from the "this->users" property
        $this->users = $this->users->filter(function($id) use ($user){
            // Loops through the "$this->users" property (it's a collection) and returns a new array with the ID different from the one passed as a parameter
            return $id != $user['id'];
        });
    }

    // Method executed to render the Livewire component without reloading the page
    public function render()
    {
        if($this->chat){

            // We update the "is_read" field to "true" in the database
            $this->chat->messages()->where('user_id', '!=', auth()->id())->where('is_read', false)->update([
                'is_read' => true
            ]);

            // We use the "Notification" Facade to send the notification to Pusher
            // 1st parameter -> ID of the users to notify
            // 2nd Parameter -> Route and Name of the created notification class (In this case called "ReadMessage")
            // Notification::send($this->getUsersNotificationsProperty(), new \App\Notifications\ReadMessage);

            // Triggers a custom Livewire event.
            // This means that when this line of code is executed, Livewire detects the "scrollIntoView" event and
            // executes all listener functions associated with that event.
            $this->emit('scrollIntoView');
        }


        // view('livewire.chat-component') => Shows the Livewire "chat" component view
        // ->layout('[Layout Route]') => Used to assign a Layout to a Livewire component.
        //      Parameter => Receives as a parameter the route of the layout you want to use, in this case "layouts.chat" was used
        return view('livewire.chat-component')->layout('layouts.chat');
    }
}
