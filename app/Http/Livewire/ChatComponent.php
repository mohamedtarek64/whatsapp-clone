<?php

namespace App\Http\Livewire;

use App\Models\Chat;
use App\Models\Contact;
use App\Models\Message;
use Livewire\Component;
use Livewire\WithFileUploads; // Added this line

// We import the Notification Library
use Illuminate\Support\Facades\Notification;
use App\Notifications\NewMessage; // Added this line
use App\Models\Story;
use App\Models\StoryView;
use App\Models\User;

class ChatComponent extends Component
{

    use WithFileUploads;

    public $search;
    public $contactChat;
    public $chat;
    public $chat_id;
    public $bodyMessage;
    public $users;

    // Group Chat properties
    public $isCreatingGroup = false;
    public $groupName;
    public $groupImage;
    public $selectedContacts = [];

    // Media properties
    public $media;

    // Pagination properties
    public $page = 1;

    // Emojis
    public $showEmojiPicker = false;
    public $showChatDetails = false;
    public $showSettings = false;
    public $showStarred = false;
    public $settingTab = 'main';
    public $replyingTo = null;
    public $showChatSearch = false;
    public $chatSearch = '';
    public $chatWallpaper;
    public $showArchived = false;
    public $showAddMember = false;
    public $memberSearch = '';
    public $showStories = false;
    public $viewingStory = null;
    public $storyContent = '';
    public $storyType = 'text';
    public $storyMedia = null;
    public $storyBgColor = '#00a884';
    public $isTyping = false;
    public $typingChatId = null;
    public $emojis = ['😀', '😃', '😄', '😁', '😆', '😅', '🤣', '😂', '🙂', '🙃', '😉', '😊', '😇', '🥰', '😍', '🤩', '😘', '😗', '😚', '😋', '😛', '😜', '🤪', '😝', '🤑', '🤗', '🤭', '🤫', '🤔', '🤐', '🤨', '😐', '😑', '😶', '😏', '😒', '🙄', '😬', '🤥', '😌', '😔', '😪', '🤤', '😴', '😷', '🤒', '🤕', '🤢', '🤮', '🤧', '🥵', '🥶', '🥴', '😵', '🤯', '🤠', '🥳', '😎', '🤓', '🧐', '😕', '😟', '🙁', '☹️', '😮', '😯', '😲', '😳', '🥺', '😦', '😧', '😨', '😰', '😥', '😢', '😭', '😱', '😖', '😣', '😞', '😓', '😩', '😫', '🥱', '😤', '😡', '😠', '🤬', '😈', '👿', '💀', '☠️', '💩', '🤡', '👹', '👺', '👻', '👽', '👾', '🤖', '😺', '😸', '😹', '😻', '😼', '😽', '🙀', '😿', '😾', '🙈', '🙉', '🙊', '💋', '💌', '💘', '💝', '💖', '💗', '💓', '💞', '💕', '💟', '❣️', '💔', '❤️', '🧡', '💛', '💚', '💙', '💜', '🤎', '🖤', '🤍', '💯', '💢', '💥', '💫', '💦', '💨', '🕳️', '💣', '💬', '👁️‍🗨️', '🗨️', '🗯️', '💭', '💤'];

    // Method executed before initializing the Livewire component "ChatComponent"
    // Initializes the "users" property as an empty collection/array to avoid errors.
    public function mount(){
        $this->users = collect();
    }

    public function addEmoji($emoji)
    {
        $this->bodyMessage .= $emoji;
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
                ->with('contactUser')
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


    // Computed property used to get chats sorted Descending by the "last_message_at" Mutator, i.e.,
    // the messages with the most recent message date will be shown first in the list
    public function getChatsProperty()
    {
        return auth()->user()->chats()
            ->withRelations()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhereHas('users', function ($uq) {
                          $uq->where('users.id', '!=', auth()->id())
                             ->where(function ($subq) {
                                 $subq->where('name', 'like', '%' . $this->search . '%')
                                      ->orWhere('email', 'like', '%' . $this->search . '%');
                             });
                      });
                });
            })
                ->where('chat_user.is_archived', $this->showArchived)
            ->orderByDesc('chat_user.is_pinned')
            ->orderByDesc('last_message_at')
            ->get();
    }

    // Computed property used when selecting a contact to show existing messages in the chat; in the view, we can use this property by calling "$this->messages"
    public function getMessagesProperty()
    {
        if (!$this->chat) {
            return collect();
        }

        return $this->chat->messages()
            ->visibleToUser()
            ->withRelations()
            ->whereDoesntHave('deletedByUsers', function ($query) {
                $query->where('users.id', auth()->id());
            })
            ->when($this->chatSearch, function($query){
                $query->where('body', 'like', '%'.$this->chatSearch.'%');
            })
            ->latest()
            ->take($this->page * 20)
            ->get()
            ->reverse();
    }

    public function getStarredMessagesProperty()
    {
        return Message::whereHas('starredByUsers', function ($query) {
            $query->where('users.id', auth()->id());
        })
        ->with(['user', 'chat'])
        ->latest()
        ->get();
    }

    public function loadMore()
    {
        $this->page++;
    }

    // Computed property used to get the User ID(s) of the Contact(s) to whom we send a message
    public function getUsersNotificationsProperty()
    {
        return $this->chat ? $this->chat->users->where('id', '!=', auth()->id()) : collect();
    }

    // Compares users who are currently active (The IDs of users currently in $this->users)
    // With the user in the chat that was entered.
    public function getActiveProperty(){
        // ->contains() => It is a collection method that determines if a given element is present in the collection or not
        if ($this->users_notifications->isEmpty()) {
            return false;
        }

        return $this->users->contains($this->users_notifications->first()->id);
    }

    public function isUserOnline($userId)
    {
        return $userId && $this->users->contains($userId);
    }
    // =================================================================================================================================================
    /* LIFE CYCLE */

    //                       and gets the new value through the "$value" attribute.
    public function updatedChatWallpaper()
    {
        $this->validate([
            'chatWallpaper' => 'image|max:5120', // 5MB Max
        ]);

        $path = $this->chatWallpaper->store('wallpapers', 'public');
        auth()->user()->update(['chat_wallpaper' => $path]);
        
        $this->reset('chatWallpaper');
        $this->emit('scrollIntoView');
    }
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
        if($value && $this->chat){
            // We use the "Notification" Facade to send the notification to Pusher
            // 1st parameter -> ID of the users to notify
            // 2nd Parameter -> Route and Name of the created notification class
            Notification::send($this->getUsersNotificationsProperty(), new \App\Notifications\UserTyping($this->chat->id));
        }
    }

    // =================================================================================================================================================
    /* FUNCTIONS EXECUTED BY AN EVENT */

    // Gets or Creates the Chat for the selected Contact
    public function open_chat_contact($contactId)
    {
        $contact = Contact::findOrFail($contactId);

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
        */

        $chatService = app(\App\Services\ChatService::class);
        $this->chat = $chatService->getOrCreateDirectChat(auth()->id(), $contact->contact_id);
        // Eager load users and message relations to prevent N+1 in the component
        $this->chat->load(['users', 'messages.user', 'messages.reactions.user']);
        
        $this->chat_id = $this->chat->id;
        $this->page = 1;
        $this->reset('contactChat', 'bodyMessage', 'search');

        $this->emit('scrollIntoView');
    }
    public function open_chat($chatId)
    {
        $chat = Chat::findOrFail($chatId);
        $this->chat = $chat;
        // Eager load users and message relations to avoid repeated queries
        $this->chat->load(['users', 'messages.user', 'messages.reactions.user']);
        $this->showChatDetails = false;
        // The ID of the open chat is stored in the "chat_id" property for use in the view
        // using the "@entangle('chat_id')" directive
        $this->chat_id = $chat->id;
        $this->page = 1;
        $this->reset('contactChat', 'bodyMessage', 'search');

        // We trigger the "scrollIntoView" event locally (in the Livewire component)
        $this->emit('scrollIntoView');

        // We update the "is_read" field to "true" in the database
        $chat->messages()->where('user_id', '!=', auth()->id())->where('is_read', false)->update([
            'is_read' => true
        ]);

        // We use the "Notification" Facade to send the notification to Pusher
        // 1st parameter -> ID of the users to notify
        // 2nd Parameter -> Route and Name of the created notification class (In this case called "ReadMessage")
        Notification::send($this->getUsersNotificationsProperty(), new \App\Notifications\ReadMessage);
    }

    public function toggleContactSelection($contactId)
    {
        if (in_array($contactId, $this->selectedContacts)) {
            $this->selectedContacts = array_diff($this->selectedContacts, [$contactId]);
        } else {
            $this->selectedContacts[] = $contactId;
        }
    }

    public function createGroup()
    {
        $this->validate([
            'groupName' => 'required|min:3',
            'selectedContacts' => 'required|array|min:1',
            'groupImage' => 'nullable|image|max:2048',
        ]);

        $imagePath = null;
        if ($this->groupImage) {
            $imagePath = $this->groupImage->store('groups', 'public');
        }

        $chatService = app(\App\Services\ChatService::class);
        $this->chat = $chatService->createGroupChat($this->groupName, $this->selectedContacts, $imagePath);
        
        $this->chat_id = $this->chat->id;
        $this->isCreatingGroup = false;
        $this->reset('groupName', 'selectedContacts', 'groupImage');
        $this->emit('scrollIntoView');
    }

    public function sendMessage()
    {
        $this->validate([
            'bodyMessage' => $this->media ? 'nullable' : 'required',
            'media' => 'nullable|max:10240',
        ]);

        if (!$this->chat) {
            $chatService = app(\App\Services\ChatService::class);
            $this->chat = $chatService->getOrCreateDirectChat(auth()->id(), $this->contactChat->contact_id);

        $this->showChatDetails = false;
        $this->chat_id = $this->chat->id;
        }

        $messageService = app(\App\Services\MessageService::class);
        $messageService->sendMessage($this->chat, $this->bodyMessage, $this->media, $this->replyingTo?->id);

        $this->reset('bodyMessage', 'media', 'replyingTo');
        $this->emit('scrollIntoView');
    }

    public function deleteForMe($messageId)
    {
        $message = Message::findOrFail($messageId);
        $messageService = app(\App\Services\MessageService::class);
        $messageService->deleteForMe($message);
    }

    public function deleteForEveryone($messageId)
    {
        $message = Message::findOrFail($messageId);
        $messageService = app(\App\Services\MessageService::class);
        $messageService->deleteForEveryone($message);
    }

    public function clearChat()
    {
        if (!$this->chat) return;

        $messageService = app(\App\Services\MessageService::class);
        $messageService->clearChat($this->chat);

        $this->showChatDetails = false;
        $this->emit('scrollIntoView');
    }

    public function blockUser()
    {
        if (!$this->chat || $this->chat->is_group) return;

        $otherUser = $this->chat->otherUser;
        if (!$otherUser) return;

        \App\Models\BlockedUser::firstOrCreate([
            'user_id' => auth()->id(),
            'blocked_id' => $otherUser->id
        ]);

        $this->showChatDetails = false;
        // Optionally, close the chat or show a message
    }

    public function togglePin($chatId)
    {
        $chat = Chat::findOrFail($chatId);
        $chat->loadMissing('users');
        $me = $chat->users->firstWhere('id', auth()->id());
        if (!$me) return;
        $chat->users()->updateExistingPivot(auth()->id(), [
            'is_pinned' => !$me->pivot->is_pinned
        ]);
    }

    public function toggleMute($chatId)
    {
        $chat = Chat::findOrFail($chatId);
        $chat->loadMissing('users');
        $me = $chat->users->firstWhere('id', auth()->id());
        if (!$me) return;

        $mutedUntil = $me->pivot->muted_until ? null : now()->addYears(100);

        $chat->users()->updateExistingPivot(auth()->id(), [
            'muted_until' => $mutedUntil
        ]);
    }

    public function toggleArchive($chatId)
    {
        $chat = Chat::findOrFail($chatId);
        $pivot = $chat->users()->where('users.id', auth()->id())->first()->pivot;
        
        $chat->users()->updateExistingPivot(auth()->id(), [
            'is_archived' => !$pivot->is_archived
        ]);

        if ($this->chat && $this->chat->id == $chatId) {
            $this->chat = null;
        }
    }

    public function toggleStar($messageId)
    {
        $message = Message::findOrFail($messageId);
        $isStarred = $message->starredByUsers()->where('user_id', auth()->id())->exists();

        if ($isStarred) {
            $message->starredByUsers()->detach(auth()->id());
        } else {
            $message->starredByUsers()->attach(auth()->id());
        }
    }

    public function toggleReaction($messageId, $emoji)
    {
        $message = Message::findOrFail($messageId);
        $reaction = $message->reactions()->where('user_id', auth()->id())->first();

        if ($reaction && $reaction->emoji == $emoji) {
            $reaction->delete();
        } else {
            $message->reactions()->updateOrCreate(
                ['user_id' => auth()->id()],
                ['emoji' => $emoji]
            );
        }
    }

    public function addMember($userId)
    {
        if (!$this->chat || !$this->chat->is_group) return;
        
        // Autenfication of the user who is adding the member
        $this->chat->loadMissing('users');
        $me = $this->chat->users->firstWhere('id', auth()->id());
        if (!$me || !$me->pivot->is_admin) return;

        $this->chat->users()->syncWithoutDetaching([$userId]);
        $this->showAddMember = false;
        $this->memberSearch = '';
    }

    public function removeMember($userId)
    {
        if (!$this->chat || !$this->chat->is_group) return;
        $this->chat->loadMissing('users');
        $me = $this->chat->users->firstWhere('id', auth()->id());
        if (!$me || !$me->pivot->is_admin) return;

        $this->chat->users()->detach($userId);
    }

    public function toggleAdmin($userId)
    {
        if (!$this->chat || !$this->chat->is_group) return;
        $this->chat->loadMissing('users');
        $me = $this->chat->users->firstWhere('id', auth()->id());
        if (!$me || !$me->pivot->is_admin) return;

        $user = $this->chat->users->firstWhere('id', $userId);
        if (!$user) return;

        $this->chat->users()->updateExistingPivot($userId, [
            'is_admin' => !$user->pivot->is_admin
        ]);
    }

    public function getAvailableContactsProperty()
    {
        if (!$this->chat) return collect();

        $existingUserIds = $this->chat->users->pluck('id');

        return Contact::where('user_id', auth()->id())
            ->whereNotIn('contact_id', $existingUserIds)
            ->when($this->memberSearch, function($query){
                $query->where('name', 'like', '%'.$this->memberSearch.'%');
            })
            ->get();
    }

    public function toggleDarkMode()
    {
        auth()->user()->update([
            'dark_mode' => !auth()->user()->dark_mode
        ]);
    }

    public function getChatStatsProperty()
    {
        if (!$this->chat) return null;

        return [
            'total_messages' => $this->chat->messages()->count(),
            'my_messages' => $this->chat->messages()->where('user_id', auth()->id())->count(),
            'media_count' => $this->chat->messages()->whereIn('type', ['image', 'file'])->count(),
            'first_message' => $this->chat->messages()->oldest()->first()?->created_at,
        ];
    }

    public function createStory()
    {
        if ($this->storyType === 'text') {
            $this->validate([
                'storyContent' => 'required|max:500',
            ]);

            Story::create([
                'user_id' => auth()->id(),
                'type' => 'text',
                'content' => $this->storyContent,
                'background_color' => $this->storyBgColor,
                'expires_at' => now()->addHours(24),
            ]);
        } else {
            $this->validate([
                'storyMedia' => 'required|file|max:10240',
            ]);

            $path = $this->storyMedia->store('stories', 'public');

            Story::create([
                'user_id' => auth()->id(),
                'type' => $this->storyType,
                'media_path' => $path,
                'expires_at' => now()->addHours(24),
            ]);
        }

        $this->reset(['storyContent', 'storyMedia', 'storyType', 'storyBgColor']);
        $this->showStories = false;
    }

    public function viewStory($storyId)
    {
        $story = Story::findOrFail($storyId);
        
        if (!$story->viewedBy(auth()->id()) && $story->user_id !== auth()->id()) {
            StoryView::create([
                'story_id' => $storyId,
                'user_id' => auth()->id(),
            ]);
        }

        $this->viewingStory = $story;
    }

    public function getStoriesProperty()
    {
        // Get stories from contacts
        $contactIds = Contact::where('user_id', auth()->id())->pluck('contact_id');
        
        return User::whereIn('id', $contactIds)
            ->orWhere('id', auth()->id())
            ->with(['activeStories' => function($query) {
                $query->latest();
            }])
            ->get()
            ->filter(function($user) {
                return $user->activeStories->count() > 0;
            });
    }

    public $profilePhoto;

    public function getTotalUnreadMessagesProperty()
    {
        return auth()->user()->chats()
            ->where('chat_user.is_archived', false)
            ->get()
            ->sum('unread_messages');
    }

    public function saveProfilePhoto()
    {
        $this->validate([
            'profilePhoto' => 'image|max:5120', // 5MB Max
        ]);

        if ($this->profilePhoto) {
            $path = $this->profilePhoto->store('profile-photos', 'public');
            auth()->user()->update([
                'profile_photo_path' => $path
            ]);
            $this->profilePhoto = null;
        }
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

    public function logout()
    {
        auth()->logout();
        session()->invalidate();
        session()->regenerateToken();
        return redirect()->route('login');
    }

    public function toggleSettings()
    {
        $this->showSettings = !$this->showSettings;
        $this->showStarred = false;
        $this->isCreatingGroup = false;
        $this->settingTab = 'main';
    }

    public function setSettingTab($tab)
    {
        $this->settingTab = $tab;
    }

    public function setReply($messageId)
    {
        $this->replyingTo = Message::find($messageId);
    }

    public function cancelReply()
    {
        $this->replyingTo = null;
    }

    public function toggleStarred()
    {
        $this->showStarred = !$this->showStarred;
        $this->showSettings = false;
        $this->isCreatingGroup = false;
    }

    // Method executed to render the Livewire component without reloading the page
    public function render()
    {
        auth()->user()->update(['last_seen_at' => now()]);

        if($this->chat){

            // We update the "is_read" field to "true" in the database
            $this->chat->messages()->where('user_id', '!=', auth()->id())->where('is_read', false)->update([
                'is_read' => true
            ]);

            // We use the "Notification" Facade to send the notification to Pusher
            // 1st parameter -> ID of the users to notify
            // 2nd Parameter -> Route and Name of the created notification class (In this case called "ReadMessage")
            Notification::send($this->getUsersNotificationsProperty(), new \App\Notifications\ReadMessage);

            // Triggers a custom Livewire event.
            // This means that when this line of code is executed, Livewire detects the "scrollIntoView" event and
            // executes all listener functions associated with that event.
            $this->emit('scrollIntoView');
        }


        // view('livewire.chat-component') => Shows the Livewire "chat" component view
        // ->layout('[Layout Route]') => Used to assign a Layout to a Livewire component.
        //      Parameter => Receives as a parameter the route of the layout you want to use, in this case "layouts.chat"
        return view('livewire.chat-component')->layout('layouts.chat');
    }
}
