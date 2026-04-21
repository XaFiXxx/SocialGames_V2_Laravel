<?php

namespace App\Models;

use App\Notifications\CustomVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'username',
        'name',
        'surname',
        'email',
        'password',
        'birthday',
        'avatar_url',
        'cover_url',
        'biography',
        'location',
        'is_admin',
        'terms_accepted_at',
        'terms_version',
        'newsletter',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'birthday' => 'date',
            'is_admin' => 'boolean',
            'password' => 'hashed',
            'terms_accepted_at' => 'datetime',
            'newsletter' => 'boolean',
        ];
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new CustomVerifyEmail());
    }

    // 🔹 GROUPS

    public function ownedGroups()
    {
        return $this->hasMany(Group::class, 'owner_id');
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    // 🔹 TEAMS

    public function ownedTeams()
    {
        return $this->hasMany(Team::class, 'owner_id');
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'team_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    // 🔹 GAMES

    public function games()
    {
        return $this->belongsToMany(Game::class, 'user_games')
            ->withPivot(['skill_level', 'favorite'])
            ->withTimestamps();
    }

    // 🔹 PLATFORMS

    public function platforms()
    {
        return $this->belongsToMany(Platform::class, 'user_platform')
            ->withTimestamps();
    }

    // 🔹 POSTS

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    // 🔹 FOLLOWS

    public function following()
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'following_id')
            ->withTimestamps();
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'follows', 'following_id', 'follower_id')
            ->withTimestamps();
    }

    // 🔹 FRIENDS

    public function sentFriendRequests()
    {
        return $this->hasMany(Friend::class, 'sender_id');
    }

    public function receivedFriendRequests()
    {
        return $this->hasMany(Friend::class, 'receiver_id');
    }

    // 🔹 CONVERSATIONS

    public function conversations()
    {
        return $this->belongsToMany(Conversation::class, 'conversation_user')
            ->withPivot(['role', 'last_read_at', 'joined_at'])
            ->withTimestamps();
    }

    // 🔹 MESSAGES

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    // 🔹 Réaction post
    public function postReactions()
    {
        return $this->hasMany(PostReaction::class);
    }

    public function postComments()
    {
        return $this->hasMany(PostComment::class);
    }
}