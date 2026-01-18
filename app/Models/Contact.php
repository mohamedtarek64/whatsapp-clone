<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    // Attributes protected by Laravel, where it expects the user to do the INSERT.
    // Every time a new field is created in the "contact" table, it must be added to this array.
    // Otherwise, you will get the message:
    // SQLSTATE[HY000]: General error: 1364 Field '[FIELD_NAME]' doesn't have a default value
    protected $fillable = [
        'name', 'user_id', 'contact_id'
    ];

    public function user()
    {
        // belongsTo() => Method that makes the "Many to One" relationship
        //  -> 1st Parameter => Model you want to relate with
        //  -> 2nd Parameter => Foreign Key you want to reference.
        //                      In this case if "contact_id" is not passed, it will take the Foreign Key "user_id" by default
        return $this->belongsTo(User::class, 'contact_id');
    }
}
