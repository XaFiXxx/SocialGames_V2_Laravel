<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'name',
        'slug',
        'tag',
        'description',
        'logo_url',
        'cover_url',
        'founded_at',
        'location',
        'website_url',
        'discord_url',
        'social_url',
        'is_active',
        'is_recruiting',
        'recruitment_message',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'founded_at' => 'date',
            'is_active' => 'boolean',
            'is_recruiting' => 'boolean',
        ];
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'team_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}