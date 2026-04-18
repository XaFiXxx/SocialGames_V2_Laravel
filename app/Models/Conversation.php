<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = [
        'type',
        'name',
        'image_url',
        'created_by',
    ];

    // Participants
    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['role', 'last_read_at', 'joined_at'])
            ->withTimestamps();
    }

    // Messages
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    // Créateur
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}