<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'game_id',
        'name',
        'slug',
        'description',
        'banner_url',
        'is_active',
        'privacy',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'group_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}