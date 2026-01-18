<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Story extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'type', 'content', 'media_path', 'background_color', 'expires_at'];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function views()
    {
        return $this->hasMany(StoryView::class);
    }

    public function viewedBy($userId)
    {
        return $this->views()->where('user_id', $userId)->exists();
    }

    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now());
    }
}
