<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Chat extends Model
{
    use HasFactory;

    /**
     * Scope to load eager relations for performance optimization
     */
    public function scopeWithRelations($query)
    {
        return $query->with(['users', 'messages.user', 'messages.reactions.user']);
    }

    /**
     * Scope to get chats for authenticated user
     */
    public function scopeForUser($query, $userId = null)
    {
        $userId = $userId ?? auth()->id();
        return $query->whereHas('users', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    // Attributes protected by Laravel, where it expects the user to do the INSERT.
    // Every time a new field is created in the "chat" table, it must be added to this array.
    // Otherwise, you will get the message:
    // SQLSTATE[HY000]: General error: 1364 Field '[FIELD_NAME]' doesn't have a default value
    protected $fillable = [
        'name', 'image_url', 'is_group', 'last_message_at'
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'is_group' => 'boolean',
    ];

    public function otherUser(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->is_group ? null : $this->users->where('id', '!=', auth()->id())->first()
        );
    }

    // =================================================================================================================================================
    /* MUTATORS */

    public function name(): Attribute
    {
        return Attribute::make(
            get: function($value){
                if($this->is_group){
                    return $value;
                }
                $user = $this->users->where('id', '!=', auth()->id())->first();

                if (!$user) {
                    return $value ?? 'Chat';
                }

                $contact = auth()->user()->contacts()->where('contact_id', $user->id)->first();
                return $contact ? $contact->name : $user->email;
            }
        );
    }

    public function image(): Attribute
    {
        return Attribute::make(
            get: function(){
                if($this->is_group){
                    return $this->image_url ? Storage::url($this->image_url) : 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7F9CF5&background=EBF4FF';
                }
                $user = $this->users->where('id', '!=', auth()->id())->first();

                if (!$user) {
                    return 'https://via.placeholder.com/150';
                }

                return $user->profile_photo_url;
            }
        );
    }

    public function lastMessageAt(): Attribute
    {
        return Attribute::make(
            get: function(){
                return $this->messages->last() ? $this->messages->last()->created_at : $this->created_at;
            }
        );
    }

    // Gets the count of messages in a chat that are not by the authenticated user and where the "is_read" field is false.
    // That is, it gets the unread messages of a chat
    public function unreadMessages(): Attribute
    {
        return Attribute::make(
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
        return $this->belongsToMany(User::class)->withPivot('is_pinned', 'muted_until', 'is_archived', 'is_admin')->withTimestamps();
    }
}
