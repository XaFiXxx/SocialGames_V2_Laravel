<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'conversation_id',
        'user_id',
        'type',
        'content',
        'image_url',
    ];

    // Conversation liée
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    // Auteur du message
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}