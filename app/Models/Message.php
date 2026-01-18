<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    // Attributes protected by Laravel, where it expects the user to do the INSERT.
    // Every time a new field is created in the "messages" table, it must be added to this array.
    // Otherwise, you will get the message:
    // SQLSTATE[HY000]: General error: 1364 Field '[FIELD_NAME]' doesn't have a default value
    protected $fillable = [
        'body', 'is_read', 'user_id', 'chat_id', 'type', 'file_path', 'deleted_for_everyone', 'parent_id'
    ];

    public function parent()
    {
        return $this->belongsTo(Message::class, 'parent_id');
    }

    public function chat(){
        // belongsTo() => Method that makes the "Many to One" relationship
        //  -> 1st Parameter => Model you want to relate with
        return $this->belongsTo(Chat::class);
    }

    public function user(){
        // belongsTo() => Method that makes the "Many to One" relationship
        //  -> 1st Parameter => Model you want to relate with
        return $this->belongsTo(User::class);
    }

    public function deletedByUsers()
    {
        return $this->belongsToMany(User::class, 'deleted_messages', 'message_id', 'user_id');
    }

    public function reactions()
    {
        return $this->hasMany(MessageReaction::class);
    }

    public function starredByUsers()
    {
        return $this->belongsToMany(User::class, 'starred_messages', 'message_id', 'user_id');
    }
}
