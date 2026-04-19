<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Genre;
use App\Models\Message;
use App\Models\Platform;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class AdminDashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        return response()->json([
            'users' => User::count(),
            'posts' => Post::count(),
            'messages' => Message::count(),
            'games' => Game::count(),
            'platforms' => Platform::count(),
            'genres' => Genre::count(),

            'new_users_24h' => User::where('created_at', '>=', now()->subDay())->count(),
            'new_posts_24h' => Post::where('created_at', '>=', now()->subDay())->count(),
        ]);
    }
}