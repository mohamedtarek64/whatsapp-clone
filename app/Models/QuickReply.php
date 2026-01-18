<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuickReply extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'shortcut', 'message'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
