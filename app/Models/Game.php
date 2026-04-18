<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'release_at',
        'developer',
        'publisher',
        'cover_img',
    ];

    protected function casts(): array
    {
        return [
            'release_at' => 'date',
        ];
    }

    public function platforms()
    {
        return $this->belongsToMany(Platform::class, 'game_platform')
            ->withTimestamps();
    }

    public function genres()
    {
        return $this->belongsToMany(Genre::class, 'game_genre')
            ->withTimestamps();
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_games')
            ->withPivot(['skill_level', 'favorite'])
            ->withTimestamps();
    }

    public function groups()
    {
        return $this->hasMany(Group::class);
    }
}