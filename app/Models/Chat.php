<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Chat extends Model
{
    use HasFactory;

    // Attributes protected by Laravel, where it expects the user to do the INSERT.
    // Every time a new field is created in the "chat" table, it must be added to this array.
    // Otherwise, you will get the message:
    // SQLSTATE[HY000]: General error: 1364 Field '[FIELD_NAME]' doesn't have a default value
    protected $fillable = [
        'name', 'image_url', 'is_group'
    ];

    // =================================================================================================================================================
    /* MUTATORS */

    public function name(): Attribute
    {
        return new Attribute(
            // Gets the name of the chat or contact
            get: function($value){
                // If the chat is a group, returns the group name
                if($this->is_group){
                    return $value;
                }
                // Otherwise
                // Gets the user with whom the current user has a conversation
                $user = $this->users->where('id', '!=', auth()->id())->first();
                // Gets the current user's contact with the found user
                $contact = auth()->user()->contacts()->where('contact_id', $user->id)->first();
                // If a contact was found, returns their name, otherwise, returns the user's email
                return $contact ? $contact->name : $user->email;
            }
        );
    }

    public function image(): Attribute
    {
        return new Attribute(
            // Gets the image of the chat or user
            get: function(){
                // If the chat is a group, returns the URL of the group image stored in Laravel storage
                if($this->is_group){
                    return Storage::url($this->image_url);
                }
                // Otherwise
                // Gets the user with whom the current user has a conversation
                $user = $this->users->where('id', '!=', auth()->id())->first();
                // Returns the user's profile photo URL
                return $user->profile_photo_url;
            }
        );
    }

    public function lastMessageAt(): Attribute
    {
        return new Attribute(
            get: function(){
                return $this->messages->last()->created_at;
            }
        );
    }

    // Gets the count of messages in a chat that are not by the authenticated user and where the "is_read" field is false.
    // That is, it gets the unread messages of a chat
    public function unreadMessages(): Attribute
    {
        return new Attribute(
            get: function(){
                return $this->messages()->where('user_id', '!=', auth()->id())->where('is_read', false)->count();
            }
        );
    }

    // =================================================================================================================================================

    public function messages()
    {
        // hasMany() => Method that makes the "One to Many" relationship
        //  -> Parameter => Model you want to relate with
        return $this->hasMany(Message::class);
    }

    public function users()
    {
        // belongsToMany() => Method that makes the "Many to Many" relationship
        return $this->belongsToMany(User::class);
    }
}
