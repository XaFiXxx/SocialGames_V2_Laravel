<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    
    public function index(Request $request)
    {
        $user = $request->user();

        $posts = Post::with(['user', 'media'])
            ->where('is_active', true)
            ->where('visibility', 'public') // simple pour commencer
            ->latest()
            ->paginate(10);

        return response()->json($posts);
    }


    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'content' => 'nullable|string|max:2000',
            'visibility' => 'required|in:public,friends,private',
            'group_id' => 'nullable|exists:groups,id',
            'team_id' => 'nullable|exists:teams,id',
        ]);

        if (empty($validated['content'])) {
            return response()->json([
                'message' => 'Le contenu ne peut pas être vide.'
            ], 422);
        }

        $post = Post::create([
            'user_id' => $user->id,
            'content' => $validated['content'],
            'visibility' => $validated['visibility'],
            'group_id' => $validated['group_id'] ?? null,
            'team_id' => $validated['team_id'] ?? null,
        ]);

        return response()->json([
            'message' => 'Post créé avec succès',
            'post' => $post->load('user')
        ], 201);
    }

    
}
