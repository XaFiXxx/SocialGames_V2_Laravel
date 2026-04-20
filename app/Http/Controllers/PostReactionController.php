<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostReaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostReactionController extends Controller
{
    public function toggle(Request $request, int $post): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:like,fire,gg'],
        ]);

        $user = $request->user();

        $postModel = Post::findOrFail($post);

        $reaction = PostReaction::where('post_id', $postModel->id)
            ->where('user_id', $user->id)
            ->first();

        if ($reaction) {
            if ($reaction->type === $validated['type']) {
                $reaction->delete();

                return response()->json([
                    'message' => 'Réaction supprimée',
                    'type' => null,
                ]);
            }

            $reaction->update([
                'type' => $validated['type'],
            ]);

            return response()->json([
                'message' => 'Réaction mise à jour',
                'type' => $validated['type'],
            ]);
        }

        PostReaction::create([
            'post_id' => $postModel->id,
            'user_id' => $user->id,
            'type' => $validated['type'],
        ]);

        return response()->json([
            'message' => 'Réaction ajoutée',
            'type' => $validated['type'],
        ]);
    }
}