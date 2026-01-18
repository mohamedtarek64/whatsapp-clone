<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserTyping extends Notification implements ShouldQueue
{
    use Queueable;

    public $user_name;
    public $user_id;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($chat_id)
    {
        $this->chat_id = $chat_id;
        $this->user_name = auth()->user()->name;
        $this->user_id = auth()->id();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['broadcast'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'chat_id' => $this->chat_id,
            'user_id' => $this->user_id,
            'user_name' => $this->user_name,
        ];
    }

    // Notifies Pusher or Laravel WebSocket (The technology used to notify in real time)
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'chat_id' => $this->chat_id,
            'user_id' => $this->user_id,
            'user_name' => $this->user_name,
        ]);
    }
}
